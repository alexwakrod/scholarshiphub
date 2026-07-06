<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\FireworksAiClient;
use App\Models\Scholarship;
use Carbon\Carbon;

class ScrapeScholarshipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;      // 1 hour
    public $tries = 1;

    private array $sources;
    private int $maxDetailPagesPerSource = 30;
    private int $maxChunkSize = 10000;

    public function __construct(array $sources = [])
    {
        $this->sources = !empty($sources)
            ? $sources
            : config('scholarship_sources.default', []);
        $this->onQueue('ai');
    }

    public function handle(FireworksAiClient $aiClient): void
    {
        $totalInserted = 0;

        foreach ($this->sources as $source) {
            try {
                Log::info("ScrapeScholarshipsJob: processing source {$source}");
                $detailUrls = $this->discoverDetailUrls($source);
                if (empty($detailUrls)) {
                    Log::warning("ScrapeScholarshipsJob: no detail URLs found on {$source}");
                    continue;
                }

                Log::info("ScrapeScholarshipsJob: found " . count($detailUrls) . " detail URLs on {$source}");

                foreach ($detailUrls as $detailUrl) {
                    try {
                        $html = $this->fetchHtml($detailUrl);
                        if (empty($html)) continue;

                        $scholarship = $this->extractSingleScholarship($aiClient, $detailUrl, $html);
                        if ($scholarship) {
                            $inserted = $this->insertIfValid($scholarship, $detailUrl);
                            $totalInserted += $inserted;
                        }
                    } catch (\Throwable $e) {
                        Log::warning("ScrapeScholarshipsJob: failed detail page {$detailUrl}: " . $e->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                Log::error("ScrapeScholarshipsJob: source {$source} failed: " . $e->getMessage());
            }
        }

          Log::info("ScrapeScholarshipsJob: total new scholarships inserted: {$totalInserted}");
        
    }

    /**
     * Fetch the main listing page and extract links to individual scholarship pages.
     */
    private function discoverDetailUrls(string $sourceUrl): array
    {
        $html = $this->fetchHtml($sourceUrl);
        if (empty($html)) return [];

        // Use AI to identify detail page links
        $prompt = <<<EOT
You are a web scraping assistant. The following text is extracted from a scholarship listing page.
Identify ALL URLs that lead to individual scholarship detail pages (not category pages, not pagination links).
Return ONLY a JSON array of full URLs.
If no detail URLs are found, return an empty array [].
EOT;

        $aiClient = app(FireworksAiClient::class);
        $response = $aiClient->complete($prompt . "\n\nTEXT:\n" . mb_substr($html, 0, 8000), [
            'temperature' => 0.1,
            'max_tokens'  => 3000,
        ]);

        $jsonStart = strpos($response, '[');
        $jsonEnd   = strrpos($response, ']');
        if ($jsonStart === false || $jsonEnd === false) return [];

        $urls = json_decode(substr($response, $jsonStart, $jsonEnd - $jsonStart + 1), true);
        if (!is_array($urls)) return [];

        // Filter and limit
        $urls = array_slice(array_filter($urls, fn($u) => filter_var($u, FILTER_VALIDATE_URL)), 0, $this->maxDetailPagesPerSource);
        return $urls;
    }

    /**
     * Fetch the HTML content of a URL.
     */
    private function fetchHtml(string $url): string
    {
        try {
            $response = Http::timeout(30)
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
                $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
                $html = preg_replace('/<!--(.*?)-->/s', '', $html);
                $html = strip_tags($html, '<p><a><h1><h2><h3><h4><h5><h6><li><ul><ol><div><span><table><tr><td><th><article><section>');
                $html = preg_replace('/\s+/', ' ', $html);
                $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
                return trim($html);
            }
        } catch (\Throwable $e) {
            Log::warning("ScrapeScholarshipsJob: HTTP fetch failed for {$url}: " . $e->getMessage());
        }
        return '';
    }

    /**
     * Extract a single scholarship from a detail page.
     */
    private function extractSingleScholarship(FireworksAiClient $aiClient, string $detailUrl, string $html): ?array
    {
        $prompt = <<<EOT
Extract the scholarship described on this page into a JSON object with these keys:
- title: string
- description: string (full text)
- provider: string
- country: string
- amount: number or null (in USD)
- deadline: string (ISO 8601 YYYY-MM-DD, must be future)
- source_url: string (use "{$detailUrl}")
- parsed_requirements: {
    academic_level: string|null,
    minimum_gpa: number|null,
    majors: string[],
    countries: string[],
    age_range: {min:int,max:int}|null,
    english_proficiency: string|null,
    other_requirements: string[]
  }
Return ONLY the JSON object, no extra text. If no scholarship found, return null.
EOT;

        $response = $aiClient->complete($prompt . "\n\nPAGE TEXT:\n" . mb_substr($html, 0, 6000), [
            'temperature' => 0.2,
            'max_tokens'  => 2500,
        ]);

        $jsonStart = strpos($response, '{');
        $jsonEnd   = strrpos($response, '}');
        if ($jsonStart === false || $jsonEnd === false) return null;

        $data = json_decode(substr($response, $jsonStart, $jsonEnd - $jsonStart + 1), true);
        return is_array($data) ? $data : null;
    }

    /**
     * Insert a single scholarship if it passes validation.
     */
    private function insertIfValid(array $item, string $source): int
    {
        try {
            if (empty($item['title']) || empty($item['description']) || empty($item['deadline'])) {
                return 0;
            }

            try {
                $deadline = Carbon::parse($item['deadline']);
                if ($deadline->isPast()) return 0;
            } catch (\Throwable) {
                return 0;
            }

            // Check for duplicate
            $exists = Scholarship::whereRaw('LOWER(title) = ?', [strtolower($item['title'])])
                ->whereRaw('LOWER(provider) = ?', [strtolower($item['provider'] ?? '')])
                ->exists();
            if ($exists) return 0;

            $parsed = $this->sanitizeRequirements($item['parsed_requirements'] ?? null);

            Scholarship::create([
                'title'               => trim($item['title']),
                'description'         => trim($item['description']),
                'provider'            => trim($item['provider'] ?? 'Unknown'),
                'country'             => trim($item['country'] ?? ''),
                'amount'              => isset($item['amount']) ? (float) $item['amount'] : null,
                'deadline'            => $deadline->toDateTimeString(),
                'source_url'          => $source,
                'parsed_requirements' => $parsed,
                'status'              => 'active',
            ]);
            return 1;
        } catch (\Throwable $e) {
            Log::warning("ScrapeScholarshipsJob: insert failed: " . $e->getMessage());
            return 0;
        }
    }

    private function sanitizeRequirements(?array $req): ?array
    {
        if (!$req) return null;
        return [
            'academic_level'      => $req['academic_level'] ?? null,
            'minimum_gpa'         => isset($req['minimum_gpa']) ? (float) $req['minimum_gpa'] : null,
            'majors'              => is_array($req['majors'] ?? null) ? $req['majors'] : [],
            'countries'           => is_array($req['countries'] ?? null) ? $req['countries'] : [],
            'age_range'           => (isset($req['age_range']) && is_array($req['age_range']))
                ? ['min' => (int)($req['age_range']['min'] ?? 0), 'max' => (int)($req['age_range']['max'] ?? 99)] : null,
            'english_proficiency' => $req['english_proficiency'] ?? null,
            'other_requirements'  => is_array($req['other_requirements'] ?? null) ? $req['other_requirements'] : [],
        ];
    }

    private function dispatchRecomputeForAllCompletedProfiles(): void
    {
        \App\Models\StudentProfile::where('profile_completed', true)
            ->chunk(100, fn($profiles) => $profiles->each(
                fn($p) => \App\Jobs\RecomputeMatchesJob::dispatch($p->id)
            ));
    }
}