<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FireworksAiClient;

class TestAiExtraction extends Command
{
    protected $signature = 'ai:test-extraction';
    protected $description = 'Test AI extraction with a sample scholarship (using enough tokens)';

    public function handle()
    {
        $ai = app(FireworksAiClient::class);

        $this->info('Testing AI connectivity...');

        try {
            $test = $ai->complete('Return only the word "OK".', ['max_tokens' => 10]);
            $this->line("Connectivity test response: " . $test);
        } catch (\Throwable $e) {
            $this->error('AI connection failed: ' . $e->getMessage());
            return 1;
        }

        $sampleHtml = <<<HTML
        <h2>Fully Funded Masters Scholarship in Germany</h2>
        <p>Provider: DAAD</p>
        <p>Country: Germany</p>
        <p>Amount: 1200 EUR per month</p>
        <p>Deadline: 2026-12-31</p>
        <p>Requirements: Bachelor's degree, IELTS 6.5, any major, age under 30, low-income preferred.</p>
HTML;

        $this->info('Sending sample to AI (max_tokens = 2000)...');
        $response = $ai->complete(
            "Extract scholarship details from this HTML into a JSON object.\n"
            . $sampleHtml . "\n"
            . "Return ONLY the JSON object with keys: title, description, provider, country, amount, deadline, parsed_requirements (array).",
            ['temperature' => 0.2, 'max_tokens' => 2000]
        );

        $this->line('Cleaned AI response:');
        $this->line($response);

        $json = json_decode($response, true);
        if ($json) {
            $this->info('Extraction successful!');
            dump($json);
        } else {
            $this->error('Failed to parse JSON. Raw output above may help debugging.');
        }

        return 0;
    }
}