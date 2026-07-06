<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Scholarship;
use App\Models\ScrapedUrl;
use App\Services\AiMatchingService;
use Carbon\Carbon;
use League\HTMLToMarkdown\HtmlConverter;

class ScrapeScholarshipsSmart extends Command
{
    protected $signature = 'scholarships:scrape-smart
                            {--source=* : Specific source URLs (comma-separated)}
                            {--ai-first : Always use AI extraction, skipping CSS selectors entirely}';
    protected $description = 'AI‑first scraper – converts HTML to Markdown, rotates Qwen models, skips recently scraped URLs';

    private AiMatchingService $aiService;
    private int $maxPages = 15;
    private HtmlConverter $htmlConverter;
    private int $scrapeCooldownDays = 14; // skip URLs scraped within this many days

    // Extensive list of trusted scholarship listing sites
    private array $rootUrls = [
        // ─── MAJOR GLOBAL AGGREGATORS ────────────────────────────────

        // Scholars4Dev – Updated listings for developing countries
        'https://www.scholars4dev.com',

        // ScholarshipRoar – Static WordPress with clear article containers
        'https://www.scholarshiproar.com',

        // International Scholarships – Comprehensive grants & scholarships
        'https://www.internationalscholarships.com',

        // Global Scholarships – 4,500+ scholarships, filter by degree/nationality
        'https://globalscholarships.com',

        // Opportunities Corners – Global scholarships & fellowships
        'https://opportunitiescorners.info',

        // ScholarshipPortal – 1,000+ scholarships in Europe & beyond
        'https://www.scholarshipportal.com',

        // Studyportals – Comprehensive scholarship finder
        'https://www.studyportals.com/scholarships',

        // ─── GOVERNMENT & OFFICIAL PROGRAMMES ─────────────────────────

        // DAAD – German Academic Exchange Service
        'https://www.daad.de/en/study-and-research-in-germany/scholarships/',

        // Fulbright – US flagship exchange programme
        'https://www.fulbrightonline.org',

        // Chevening – UK government scholarships
        'https://www.chevening.org/scholarships',

        // Erasmus+ – EU-funded opportunities
        'https://erasmus-plus.ec.europa.eu/opportunities',

        // British Council – UK-focused grants
        'https://www.britishcouncil.org/study-work-abroad/scholarships',

        // ─── GRADUATE & PHD FOCUS ──────────────────────────────────────

        // PhDportal – PhD programs and scholarships worldwide
        'https://www.phdportal.com',

        // MastersPortal – Master's programs & funding
        'https://www.mastersportal.com',

        // Postgraduate-Funding – Alternative funding for postgrads
        'https://www.postgraduate-funding.com',

        // ProFellow – Fellowships & fully funded graduate programs
        'https://www.profellow.com',

        // GoGrad – Graduate scholarships & grants
        'https://www.gograd.org',

        // Global Study Road – Comprehensive platform for scholarships
        'https://www.globalstudyroad.com',

        // ─── RESEARCH & ACADEMIC POSITIONS ─────────────────────────────

        // EURAXESS – EU research positions & funding
        'https://euraxess.ec.europa.eu',

        // Academic Positions – Academic jobs & funded research
        'https://www.academicpositions.com',

        // ─── COUNTRY-SPECIFIC / REGIONAL ──────────────────────────────

        // Buddy4Study – 10,000+ scholarships for Indian & international students
        'https://www.buddy4study.com',

        // Scholarship Region – Curated list of fully funded scholarships
        'https://www.scholarshipregion.com',

        // ScholarshipBob – Fully funded scholarships for international students
        'https://www.scholarshipbob.com',

        // ─── CATEGORY PAGES (DEEPER COVERAGE) ─────────────────────────

        // Scholars4Dev category pages
        'https://www.scholars4dev.com/category/fully-funded-scholarships/',
        'https://www.scholars4dev.com/category/scholarships-by-country/',

        // ScholarshipRoar category pages
        'https://www.scholarshiproar.com/scholarships',
        'https://www.scholarshiproar.com/fully-funded-scholarships',

        // Global Scholarships category
        'https://globalscholarships.com/scholarships',

        // International Scholarships category
        'https://www.internationalscholarships.com/scholarships',

        // IEFA category
        'https://www.iefa.org/scholarships',

        // ScholarshipRegion category
        'https://www.scholarshipregion.com/category/scholarships/',
        'https://www.scholarshipregion.com/fully-funded-scholarships',

        // ScholarshipBob category
        'https://www.scholarshipbob.com/fully-funded-scholarships',
    ];

    public function handle(): int
    {
        $this->aiService = app(AiMatchingService::class);
        $this->htmlConverter = new HtmlConverter([
            'strip_tags' => true,
            'remove_nodes' => 'script, style, nav, footer, header, aside, .sidebar, .noise, .advertisement',
        ]);

        $sources = $this->option('source') ?: $this->discoverSources();
        if (empty($sources)) {
            $this->error('No sources found.');
            return self::FAILURE;
        }
        $this->info('AI-first scraping ' . count($sources) . ' sources.');

        $totalInserted = 0;

        foreach ($sources as $sourceUrl) {
            $this->info("Processing: {$sourceUrl}");

            // Skip if recently scraped
            if (ScrapedUrl::isRecentlyScraped($sourceUrl, $this->scrapeCooldownDays)) {
                $this->line("  Skipping – scraped within the last {$this->scrapeCooldownDays} days.");
                continue;
            }

            if (!$this->urlExists($sourceUrl)) {
                $this->warn("  URL unreachable, skipping.");
                continue;
            }

            $currentUrl = $sourceUrl;
            $page = 1;
            $consecutiveEmpty = 0;
            while ($currentUrl && $page <= $this->maxPages) {
                // Also skip individual page if recently scraped
                if (ScrapedUrl::isRecentlyScraped($currentUrl, $this->scrapeCooldownDays)) {
                    $this->line("  Page {$page} already scraped recently, moving on.");
                    $currentUrl = $this->findNextPageUrl($currentUrl);
                    $page++;
                    continue;
                }

                $this->line("  Page {$page}: {$currentUrl}");
                $inserted = $this->scrapePageWithAI($currentUrl);
                $totalInserted += $inserted;
                $this->info("  -> {$inserted} scholarships added.");

                // Mark the URL as scraped now
                ScrapedUrl::markScraped($currentUrl);

                if ($inserted === 0) {
                    $consecutiveEmpty++;
                    if ($consecutiveEmpty >= 2) {
                        $this->line("  No new items for 2 pages, moving on.");
                        break;
                    }
                } else {
                    $consecutiveEmpty = 0;
                }

                $currentUrl = $this->findNextPageUrl($currentUrl);
                if (!$currentUrl) break;
                $page++;
            }

            // Mark the main source URL as scraped as well
            ScrapedUrl::markScraped($sourceUrl);
        }

        $this->newLine()->info("Total new scholarships: {$totalInserted}");
        if ($totalInserted > 0) {
            $this->info('Dispatching match recomputation...');
            \App\Models\StudentProfile::where('profile_completed', true)
                ->chunk(100, fn($profiles) => $profiles->each(
                    fn($p) => \App\Jobs\RecomputeMatchesJob::dispatch($p->id)
                ));
        }

        return self::SUCCESS;
    }

    /**
     * Scrape a single page: convert HTML to Markdown, send to Qwen AI.
     */
    private function scrapePageWithAI(string $url): int
    {
        $html = $this->httpGet($url);
        if (empty($html)) return 0;

        // Convert HTML to Markdown to reduce token cost
        $markdown = $this->htmlToMarkdown($html);
        $truncated = mb_substr($markdown, 0, 12000); // ~3k tokens

        $this->line("      Sending page (Markdown, ~" . strlen($truncated) . " chars) to AI...");
        $modelUsed = null;
        $scholarships = $this->aiExtractScholarships($truncated, $url, $modelUsed);
        $this->line("      Model used: {$modelUsed}");

        if (empty($scholarships)) {
            $this->warn("      No scholarships found by AI.");
            return 0;
        }

        $inserted = 0;
        foreach ($scholarships as $item) {
            if (empty($item['title']) || empty($item['description'])) continue;

            $deadline = $item['deadline'] ?? null;
            if ($deadline) {
                try {
                    $deadlineObj = Carbon::parse($deadline);
                    if ($deadlineObj->isPast()) continue;
                } catch (\Throwable) {}
            } else {
                $deadlineObj = now()->addYear(); // default if missing
            }

            $exists = Scholarship::where('title', $item['title'])
                        ->where('provider', $item['provider'] ?? '')
                        ->exists();
            if ($exists) continue;

            Scholarship::create([
                'title'               => $item['title'],
                'description'         => $item['description'],
                'provider'            => $item['provider'] ?? 'Unknown',
                'country'             => $item['country'] ?? '',
                'amount'              => $item['amount'] ?? null,
                'deadline'            => $deadlineObj instanceof Carbon ? $deadlineObj->toDateTimeString() : now()->addYear()->toDateTimeString(),
                'source_url'          => $item['source_url'] ?? $url,
                'parsed_requirements' => $item['parsed_requirements'] ?? null,
                'status'              => 'active',
            ]);
            $inserted++;
        }

        return $inserted;
    }

    /**
     * Convert HTML to clean Markdown, stripping noise.
     */
    private function htmlToMarkdown(string $html): string
    {
        try {
            $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
            $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
            $html = preg_replace('/<!--(.*?)-->/s', '', $html);

            $noisePatterns = [
                'Scholarships by Closing Date', 'Scholarships Closing Today', 'Scholarships Closing This Week',
                'Scholarships by Category', 'Scholarships by Country', 'Scholarships by School',
                'Scholarships by Organization', 'Scholarships for African Students', 'Female Scholarships',
                'Latest Scholarships', 'Scholarships You May Like', 'Share this scholarship',
                'Subscribe for scholarship update', 'Table of Content', '×', 'LIVE MASTERCLASS',
                'Free Ongoing Fully Funded Scholarships Guide', 'Join our Scholarship Success Masterclass',
                'Scholarship Tips', 'Video Guides', 'Best Scholarships', 'Apply for Scholarships',
                'Scholarships by Country to Study', 'Scholarships by Course',
                'Scholarships by Institution / Company', 'Services / Resources', 'Scholarships In',
                'Copyright ©', 'Be among the first to know whenever new', 'Join us on social media',
                'WhatsApp', 'Telegram', 'Facebook', 'Twitter', 'LinkedIn', 'YouTube',
                'Blog', 'Events', 'Essay Templates', 'See more latest scholarships',
                'Menu', 'Subscribe', 'Enter you email', 'Scholarships For', 'Scholarships by Category',
            ];
            foreach ($noisePatterns as $noise) {
                $html = str_replace($noise, '', $html);
            }

            return $this->htmlConverter->convert($html);
        } catch (\Throwable $e) {
            $this->warn("Markdown conversion failed: " . $e->getMessage());
            return strip_tags($html); // fallback
        }
    }

    /**
     * Ask Qwen to extract all scholarships from the Markdown text.
     */
    private function aiExtractScholarships(string $markdown, string $sourceUrl, ?string &$usedModel = null): array
    {
        $prompt = <<<EOT
Extract ALL scholarships, fellowships, grants, or funding opportunities listed in the Markdown below.
Return a JSON array of objects, each with these keys:
- title: string
- description: string (all relevant text, with Markdown formatting preserved for readability)
- provider: string (organization offering the scholarship)
- country: string (where the study takes place)
- amount: number|null (in USD if possible)
- deadline: string (YYYY-MM-DD if available, else null)
- source_url: string (use "{$sourceUrl}" or the specific detail URL if found)
- parsed_requirements: object with:
    - academic_level: string|null
    - minimum_gpa: number|null
    - majors: array of strings
    - eligible_countries: array of strings
    - english_proficiency: string|null
    - age_range: {min:int, max:int}|null
    - other_requirements: array of strings
    - benefit_type: string|null
    - duration: string|null

If no opportunities are found, return an empty array [].
Do not include any extra text, just the JSON array.
EOT;

        $response = $this->aiService->prompt($prompt . "\n\nMARKDOWN:\n" . $markdown, [
            'max_tokens'  => 4000,
            'temperature' => 0.2,
        ], $usedModel);

        if (!$response) return [];

        $data = json_decode($response, true);
        return is_array($data) ? $data : [];
    }

    // ─── HELPERS ────────────────────────────────────────────────────

    private function httpGet(string $url): string
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                ->get($url);

            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Throwable) {}
        return '';
    }

    private function urlExists(string $url): bool
    {
        try {
            return Http::timeout(10)->head($url)->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    private function findNextPageUrl(string $currentUrl): ?string
    {
        $html = $this->httpGet($currentUrl);
        if (empty($html)) return null;

        // rel="next"
        if (preg_match('/<a[^>]+rel="next"[^>]+href="([^"]+)"/', $html, $m)) {
            return $m[1];
        }
        // /page/2/ pattern
        if (preg_match('#/page/(\d+)/?#', $currentUrl, $m)) {
            $next = (int)$m[1] + 1;
            return preg_replace('#/page/\d+/?#', "/page/{$next}/", $currentUrl);
        }
        // ?page=2
        if (preg_match('/[?&]page=(\d+)/', $currentUrl, $m)) {
            $next = (int)$m[1] + 1;
            return preg_replace('/([?&]page=)\d+/', '$1'.$next, $currentUrl);
        }
        return null;
    }

    /**
     * Discover scholarship listing URLs using AI search.
     */
    private function discoverSources(): array
    {
        $this->info("Discovering listing pages via AI search...");
        $queries = [
            'fully funded scholarships 2026 2027 list',
            'international scholarships for developing countries 2026',
            'masters scholarships 2026 deadline',
            'phd fellowships 2026 international students',
            'undergraduate scholarships 2026 fully funded',
        ];

        $discovered = [];
        foreach ($queries as $query) {
            $this->line("  AI search: {$query}");
            $searchPrompt = <<<EOT
Search the web for scholarship listing pages that contain multiple scholarship opportunities.
For the query "{$query}", return a JSON array of up to 5 valid URLs that actually list scholarships.
Only include pages that are directly accessible (no login required).
EOT;
            $urlsJson = $this->aiService->prompt($searchPrompt, ['max_tokens' => 800, 'temperature' => 0.3]);
            if ($urlsJson) {
                $urls = json_decode($urlsJson, true);
                if (is_array($urls)) {
                    foreach ($urls as $url) {
                        if ($this->urlExists($url)) {
                            $discovered[] = $url;
                        }
                    }
                }
            }
        }

        // Also add the extensive rootUrls list
        foreach ($this->rootUrls as $url) {
            $discovered[] = $url;
        }

        return array_values(array_unique($discovered));
    }
}