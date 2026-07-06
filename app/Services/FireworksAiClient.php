<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FireworksAiClient
{
    private string $baseUrl;
    private string $apiKey;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.fireworks.base_url', 'https://api.fireworks.ai/inference/v1');
        $this->apiKey  = config('services.fireworks.api_key', '');
        $this->model   = config('services.fireworks.model', 'accounts/fireworks/models/llama-v3-70b-instruct');
        $this->timeout = 120;
    }

    /**
     * Send a completion request to Fireworks AI.
     *
     * @param  string $prompt
     * @param  array  $options  temperature, max_tokens, top_p
     * @return string Cleaned generated text (JSON only, no reasoning).
     *
     * @throws \Exception
     */
    public function complete(string $prompt, array $options = []): string
    {
        $attempts      = 0;
        $maxRetries    = 2;
        $lastException = null;

        while ($attempts <= $maxRetries) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->timeout($this->timeout)
                ->post($this->baseUrl . '/chat/completions', [
                    'model'       => $this->model,
                    'messages'    => [
                        [
                            'role'    => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => $options['temperature'] ?? 0.2,
                    'max_tokens'  => $options['max_tokens'] ?? 2000,
                    'top_p'       => $options['top_p'] ?? 1,
                ]);

                if ($response->successful()) {
                    $body    = $response->json();
                    $content = $body['choices'][0]['message']['content'] ?? '';

                    if (empty(trim($content))) {
                        throw new \Exception('AI response content is empty.');
                    }

                    return $this->cleanResponse($content);
                }

                $status    = $response->status();
                $errorBody = $response->body();

                Log::warning('Fireworks AI API error.', [
                    'status'  => $status,
                    'body'    => $errorBody,
                    'attempt' => $attempts + 1,
                ]);

                if ($status >= 500 || $status === 429) {
                    $attempts++;
                    if ($attempts <= $maxRetries) {
                        sleep(1 * $attempts);
                        continue;
                    }
                }

                throw new \Exception("Fireworks AI request failed with status {$status}: " . $errorBody);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('Fireworks AI connection exception: ' . $e->getMessage(), ['attempt' => $attempts + 1]);
                $attempts++;
                if ($attempts > $maxRetries) {
                    $lastException = $e;
                    break;
                }
                sleep(2);
            } catch (\Throwable $e) {
                Log::error('Fireworks AI client error: ' . $e->getMessage(), [
                    'attempt' => $attempts + 1,
                    'trace'   => $e->getTraceAsString(),
                ]);
                $lastException = $e;
                break;
            }
        }

        throw $lastException ?? new \Exception('Fireworks AI request failed after retries.');
    }

    /**
     * Strip all reasoning / preamble text that the model may output
     * before the actual JSON content. Returns only the JSON portion.
     */
    protected function cleanResponse(string $raw): string
    {
        // Locate the first JSON character
        $firstBrace   = strpos($raw, '{');
        $firstBracket = strpos($raw, '[');

        if ($firstBrace === false && $firstBracket === false) {
            // No JSON at all – return raw for debugging
            return trim($raw);
        }

        // Determine which comes first
        if ($firstBrace === false) {
            $start = $firstBracket;
        } elseif ($firstBracket === false) {
            $start = $firstBrace;
        } else {
            $start = min($firstBrace, $firstBracket);
        }

        $json = substr($raw, $start);

        // Also strip trailing text after the closing brace/bracket (if any)
        $lastBrace   = strrpos($json, '}');
        $lastBracket = strrpos($json, ']');

        if ($lastBrace === false && $lastBracket === false) {
            return trim($json);
        }

        if ($lastBrace === false) {
            $end = $lastBracket;
        } elseif ($lastBracket === false) {
            $end = $lastBrace;
        } else {
            $end = max($lastBrace, $lastBracket);
        }

        return trim(substr($json, 0, $end + 1));
    }
}