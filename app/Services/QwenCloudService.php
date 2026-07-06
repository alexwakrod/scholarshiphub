<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\StudentProfile;

class QwenCloudService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected array $models;
    protected int $timeout;
    protected bool $fireworksFallback;
    protected int $maxModelAttempts;
    protected string $modelIndexCacheKey = 'qwen_model_round_robin_index';
    private array $modelUsage = [];

    public function __construct()
    {
        $this->baseUrl          = config('qwen_cloud.base_url');
        $this->apiKey           = config('qwen_cloud.api_key');
        $this->models           = config('qwen_cloud.models', []);
        $this->timeout          = config('qwen_cloud.timeout', 30);
        $this->fireworksFallback = config('qwen_cloud.fireworks_fallback', true);

        if (empty($this->models)) {
            Log::critical('QwenCloudService: no models configured. All AI requests will fail.');
        }

        $this->maxModelAttempts = count($this->models);

        foreach ($this->models as $model) {
            $this->modelUsage[$model] = 0;
        }
    }

    // ----------------------------------------------------------------
    // PUBLIC API (existing methods)
    // ----------------------------------------------------------------

    public function validateScholarship(array $data): ?array
    {
        $result = $this->classify($data);
        if ($result === null && $this->fireworksFallback) {
            Log::info('QwenCloudService: falling back to Fireworks for validation.');
            return app(FireworksAiClient::class)->complete(
                "Classify this scholarship: " . json_encode($data),
                ['max_tokens' => 300]
            );
        }
        return $result;
    }

    public function extractRequirements(array $data): ?array
    {
        $result = $this->extract($data);
        if ($result === null && $this->fireworksFallback) {
            Log::info('QwenCloudService: falling back to Fireworks for extraction.');
        }
        return $result;
    }

    public function matchStudentToScholarships(StudentProfile $student, array $scholarshipIds): array
    {
        return [];
    }

    public function getUsageStats(): array
    {
        return $this->modelUsage;
    }

    // ----------------------------------------------------------------
    // CORE: Classification & Extraction
    // ----------------------------------------------------------------

    private function classify(array $data): ?array
    {
        $prompt = $this->buildClassificationPrompt(
            $data['title'],
            $data['description'],
            $data['provider'] ?? ''
        );

        $response = $this->sendChatRequestRotating('', $prompt, 400);
        if ($response === null) {
            return null;
        }

        return $this->parseClassificationOutput($response);
    }

    private function extract(array $data): ?array
    {
        $prompt = $this->buildExtractionPrompt(
            $data['title'],
            $data['description'],
            $data['provider'] ?? ''
        );

        $response = $this->sendChatRequestRotating('', $prompt, 600);
        if ($response === null) {
            return null;
        }

        $jsonStart = strpos($response, '{');
        $jsonEnd   = strrpos($response, '}');
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonStr = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $parsed  = json_decode($jsonStr, true);
            if (is_array($parsed)) {
                return $parsed;
            }
        }

        return null;
    }

    // ----------------------------------------------------------------
    // ROTATING CHAT REQUEST
    // ----------------------------------------------------------------

    public function sendChatRequestRotating(
        string $model,
        string $prompt,
        int    $maxTokens    = 400,
        float  $temperature  = 0.1
    ): ?string {
        $startIndex = $this->getPersistentModelIndex();
        $triedModels = [];

        for ($attempt = 0; $attempt < $this->maxModelAttempts; $attempt++) {
            $currentIndex = ($startIndex + $attempt) % count($this->models);
            $currentModel = $this->models[$currentIndex];
            $triedModels[] = $currentModel;

            $payload = [
                'model'       => $currentModel,
                'messages'    => [
                    ['role' => 'system', 'content' => 'You are a scholarship data extractor. Always respond with valid JSON.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
                'temperature' => $temperature,
                'max_tokens'  => $maxTokens,
            ];

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->timeout($this->timeout)
                ->post($this->baseUrl . '/chat/completions', $payload);

                if ($response->successful()) {
                    $this->modelUsage[$currentModel]++;
                    $this->advancePersistentCounter();

                    $body = $response->json();
                    return $body['choices'][0]['message']['content'] ?? null;
                }

                $status = $response->status();
                Log::warning(
                    "Qwen Cloud request failed on model {$currentModel}",
                    ['status' => $status, 'attempt' => $attempt + 1]
                );

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::warning(
                    "Qwen connection error on model {$currentModel}: " . $e->getMessage(),
                    ['attempt' => $attempt + 1]
                );
            } catch (\Throwable $e) {
                Log::error(
                    "Unexpected error using model {$currentModel}: " . $e->getMessage(),
                    ['attempt' => $attempt + 1]
                );
            }
        }

        Log::error('QwenCloudService: all models exhausted for prompt.', [
            'tried_models' => $triedModels,
            'prompt_preview' => substr($prompt, 0, 200),
        ]);

        return null;
    }

    /**
     * NEW: Streaming chat completion – uses curl for true SSE streaming.
     */
    public function sendChatRequestStreaming(
        string $model,
        string $prompt,
        int    $maxTokens = 400,
        float  $temperature = 0.1,
        callable $onData = null
    ): void {
        $url = rtrim($this->baseUrl, '/') . '/chat/completions';

        $payload = [
            'model'       => $this->models[0] ?? 'qwen-plus',
            'messages'    => [
                ['role' => 'system', 'content' => 'You are a scholarship data extractor. Always respond with valid JSON.'],
                ['role' => 'user',   'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens,
            'stream'      => true,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use ($onData) {
            foreach (explode("\n", $data) as $line) {
                $line = trim($line);
                if (str_starts_with($line, 'data:')) {
                    $json = substr($line, 5);
                    if ($json === '[DONE]') {
                        return strlen($data);
                    }
                    $decoded = json_decode($json, true);
                    $content = $decoded['choices'][0]['delta']['content'] ?? '';
                    if ($content !== '' && $onData) {
                        call_user_func($onData, $content);
                    }
                }
            }
            return strlen($data);
        });
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_exec($ch);
        curl_close($ch);
    }

    public function sendChatRequest(
        string $model,
        string $prompt,
        int    $maxTokens    = 400,
        float  $temperature  = 0.1
    ): ?string {
        return $this->sendChatRequestRotating($model, $prompt, $maxTokens, $temperature);
    }

    // ----------------------------------------------------------------
    // PERSISTENT ROUND‑ROBIN COUNTER (unchanged)
    // ----------------------------------------------------------------

    private function getPersistentModelIndex(): int
    {
        try {
            $counter = Cache::get($this->modelIndexCacheKey, 0);
            Cache::increment($this->modelIndexCacheKey);
            return ($counter % count($this->models));
        } catch (\Throwable $e) {
            Log::error('QwenCloudService: cache error during counter fetch: ' . $e->getMessage());
            static $fallbackIndex = 0;
            return ($fallbackIndex++ % count($this->models));
        }
    }

    private function advancePersistentCounter(): void
    {
        try {
            Cache::increment($this->modelIndexCacheKey);
        } catch (\Throwable $e) {
            Log::error('QwenCloudService: cache error advancing counter: ' . $e->getMessage());
        }
    }

    // ----------------------------------------------------------------
    // PROMPT BUILDERS (unchanged)
    // ----------------------------------------------------------------

    private function buildClassificationPrompt(string $title, string $description, string $provider): string
    {
        return <<<EOT
Classify the following scholarship description and extract structured fields.
Return ONLY a valid JSON object with these keys (no extra text):
- is_valid (boolean)
- confidence (float 0-1)
- deadline (YYYY-MM-DD or null)
- provider (string or null)
- amount (string or null)
- education_level (array of strings)
- gpa_required (float or null)
- majors (array of strings)
- eligible_countries (array of strings)
- age_range (object with min and max, or null)
- english_proficiency (string or null)
- other_requirements (array of strings)
- benefit_type (string or null)
- duration (string or null)
- match_score (float 0-1)
- match_reasons (array of strings)

Student profile for matching:
- Academic Level: undergraduate
- Major: Computer Science
- GPA: 3.7
- Country: Egypt
- English: IELTS 7.0
- Interests: fully_funded, europe, engineering, technology

Scholarship:
Title: {$title}
Description: {$description}
Provider: {$provider}
EOT;
    }

    private function buildExtractionPrompt(string $title, string $description, string $provider): string
    {
        return <<<EOT
Extract detailed eligibility requirements from this scholarship.
Return ONLY a JSON object with these keys:
- education_level (array of strings)
- gpa_required (float or null)
- majors (array of strings)
- eligible_countries (array of strings)
- age_range (object with min and max, or null)
- english_proficiency (string or null)
- other_requirements (array of strings)
- duration (string or null)
- benefit_type (string or null)

Scholarship:
Title: {$title}
Description: {$description}
Provider: {$provider}
EOT;
    }


    /**
     * Stream a chat completion token-by-token, calling $onProgress for each chunk.
     */
    public function streamChatCompletion(
        string $model,       // ignored – uses first model
        string $prompt,
        int    $maxTokens = 400,
        float  $temperature = 0.1,
        callable $onProgress = null
    ): string
    {
        $url = rtrim($this->baseUrl, '/') . '/chat/completions';

        $payload = [
            'model'       => $this->models[0] ?? 'qwen-plus',
            'messages'    => [
                ['role' => 'system', 'content' => 'You are a scholarship data extractor. Always respond with valid JSON.'],
                ['role' => 'user',   'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens,
            'stream'      => true,
        ];

        $client = new \GuzzleHttp\Client(['timeout' => $this->timeout + 60]);
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ],
            'json'    => $payload,
            'stream'  => true,
        ]);

        $body = $response->getBody();
        $fullText = '';

        while (!$body->eof()) {
            $line = $this->readLine($body);
            if (str_starts_with($line, 'data:')) {
                $data = json_decode(trim(substr($line, 5)), true);
                $content = $data['choices'][0]['delta']['content'] ?? '';
                if ($content !== '') {
                    $fullText .= $content;
                    if ($onProgress) {
                        call_user_func($onProgress, $content);
                    }
                }
            }
        }

        return $fullText;
    }

    /**
     * Read a single line from a PSR-7 stream.
     */
    private function readLine($stream): string
    {
        $line = '';
        while (!$stream->eof()) {
            $char = $stream->read(1);
            if ($char === "\n") {
                break;
            }
            if ($char !== "\r") {
                $line .= $char;
            }
        }
        return $line;
    }


    // ----------------------------------------------------------------
    // RESPONSE PARSER (unchanged)
    // ----------------------------------------------------------------

    private function parseClassificationOutput(string $text): ?array
    {
        $jsonStart = strpos($text, '{');
        $jsonEnd   = strrpos($text, '}');
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonStr = substr($text, $jsonStart, $jsonEnd - $jsonStart + 1);
            $parsed  = json_decode($jsonStr, true);
            if (is_array($parsed)) {
                return array_merge([
                    'is_valid'           => false,
                    'confidence'         => 0,
                    'deadline'           => null,
                    'provider'           => null,
                    'amount'             => null,
                    'education_level'    => [],
                    'gpa_required'       => null,
                    'majors'             => [],
                    'eligible_countries' => [],
                    'age_range'          => null,
                    'english_proficiency'=> null,
                    'other_requirements' => [],
                    'benefit_type'       => null,
                    'duration'           => null,
                    'match_score'        => 0,
                    'match_reasons'      => [],
                    'requirements'       => $parsed, // include raw
                ], $parsed);
            }
        }
        return null;
    }
}