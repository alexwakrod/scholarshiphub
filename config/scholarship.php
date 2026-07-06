<?php

return [
    'match_weights' => [
        'academic'     => 40,
        'demographic'  => 30,
        'interest'     => 20,
        'eligibility'  => 10,
    ],

    'ai' => [
        'model'       => env('FIREWORKS_AI_MODEL', 'accounts/fireworks/models/llama-v3p1-70b-instruct'),
        'api_key'     => env('FIREWORKS_API_KEY'),
        'base_url'    => env('FIREWORKS_BASE_URL', 'https://api.fireworks.ai/inference/v1'),
        'max_tokens'  => 3000,
        'temperature' => 0.2,
        // AI‑powered strict eligibility verification – off by default to save tokens
        'eligibility_verification_enabled' => env('AI_MATCH_ELIGIBILITY_ENABLED', false),
    ],

    'scrape_use_browser' => env('SCHOLARSHIP_SCRAPE_USE_BROWSER', false),

    'rate_limits' => [
        'document_reviews_per_day'   => env('DOCUMENT_REVIEWS_PER_DAY', 10),
        'pathway_generations_per_hour' => env('PATHWAY_GENERATIONS_PER_HOUR', 1),
    ],

    'cache_ttls' => [
        'match_scores'       => 86400,
        'landing_stats'      => 3600,
        'scholarships_list'  => 3600,
    ],

    'import' => [
        'chunk_size'      => 100,
        'max_upload_size' => 10240,
    ],

    'upload' => [
        'max_file_size'    => 10240,
        'allowed_mime_types' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ],
    ],

    'notification_types' => [
        'deadline_reminder',
        'new_match',
        'review_completed',
    ],
];