<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Local AI Service Configuration
    |--------------------------------------------------------------------------
    |
    | Connection details for the Qwen2.5‑1.5B FastAPI service running on the
    | same machine. The service listens on 127.0.0.1:8000 by default.
    |
    */
    'url'     => env('AI_SERVICE_URL', 'http://127.0.0.1:8000'),
    'timeout' => env('AI_SERVICE_TIMEOUT', 60),      // seconds
    'enabled' => env('AI_SERVICE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Retry & Backoff
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'times' => env('AI_SERVICE_RETRY', 2),
        'sleep' => env('AI_SERVICE_RETRY_SLEEP', 500), // milliseconds
    ],
];