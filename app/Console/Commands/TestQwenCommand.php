<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiMatchingService;

class TestQwenCommand extends Command
{
    protected $signature = 'ai:test-qwen';
    protected $description = 'Test the Qwen Cloud AI service';

    public function handle()
    {
        $ai = app(AiMatchingService::class);

        $this->info('Testing Qwen Cloud connectivity...');

        // 1. Test a simple prompt
        $response = $ai->prompt('Return only the word "OK".', ['max_tokens' => 10, 'model' => 'qwen-flash-2025-07-28']);
        if ($response === null) {
            $this->error('Simple prompt failed – check API key, network, and model name.');
            return 1;
        }
        $this->line("Simple prompt response: " . trim($response));

        // 2. Test classification
        $this->info('Testing classification...');
        $result = $ai->validateScholarship([
            'title'       => 'Fully Funded Masters Scholarship in Germany',
            'description' => 'DAAD scholarship for international students. Deadline: 2026-12-31. IELTS 6.5 required.',
            'provider'    => 'DAAD',
        ]);

        if ($result === null) {
            $this->error('Classification failed.');
            return 1;
        }
        $this->line('Classification result: ' . json_encode($result, JSON_PRETTY_PRINT));

        // 3. Test requirement extraction
        $this->info('Testing requirement extraction...');
        $reqs = $ai->extractRequirements([
            'title'       => 'Fully Funded Masters Scholarship in Germany',
            'description' => 'DAAD scholarship for international students. Deadline: 2026-12-31. IELTS 6.5 required. Bachelor degree needed.',
            'provider'    => 'DAAD',
        ]);
        $this->line('Extraction result: ' . json_encode($reqs, JSON_PRETTY_PRINT));

        $this->info('All tests passed.');
        return 0;
    }
}