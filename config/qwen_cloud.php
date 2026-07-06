<?php

return [
    'api_key' => env('QWEN_API_KEY', ''),
    'base_url' => env('QWEN_API_BASE', 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1'),

    /*
    |--------------------------------------------------------------------------
    | Model Pool for Round‑Robin Rotation
    |--------------------------------------------------------------------------
    |
    | Every model listed here is tried exactly once per request, in the order
    | defined, until a successful response is received.  The starting position
    | is determined by a persistent Redis counter shared across all workers.
    |
    | Only text chat‑completion models are included.  VL / OCR / TTS / video /
    | machine‑translation / live‑translate / omni models are excluded.
    |
    | The current list contains 80+ models, all with active free quotas on
    | the DashScope international endpoint.
    |
    */
    'models' => [

        // ── Qwen‑Turbo (fast, cost‑effective) ─────────────────────
        'qwen-turbo',
        'qwen-turbo-latest',
        'qwen-turbo-2025-04-28',

        // ── Qwen‑Plus (balanced) ──────────────────────────────────
        'qwen-plus',
        'qwen-plus-latest',
        'qwen-plus-2025-07-28',
        'qwen-plus-2025-04-28',
        'qwen-plus-2025-12-01',
        'qwen-plus-character',

        // ── Qwen‑Max (most capable) ───────────────────────────────
        'qwen-max',
        'qwen-max-2025-01-25',
        'qwen3-max',
        'qwen3-max-preview',
        'qwen3-max-2025-09-23',
        'qwen3-max-2026-01-23',

        // ── Qwen‑Flash (ultra‑fast) ───────────────────────────────
        'qwen-flash',
        'qwen-flash-2025-07-28',
        'qwen-flash-character',

        // ── Qwen2.5 Instruct (various sizes) ──────────────────────
        'qwen2.5-7b-instruct',
        'qwen2.5-7b-instruct-1m',
        'qwen2.5-14b-instruct',
        'qwen2.5-32b-instruct',
        'qwen2.5-72b-instruct',

        // ── Qwen3 Instruct ────────────────────────────────────────
        'qwen3-0.6b',
        'qwen3-8b',
        'qwen3-14b',
        'qwen3-32b',
        'qwen3-30b-a3b',
        'qwen3-30b-a3b-instruct-2507',
        'qwen3-235b-a22b',
        'qwen3-235b-a22b-instruct-2507',

        // ── Qwen3 Thinking ────────────────────────────────────────
        'qwen3-30b-a3b-thinking-2507',
        'qwen3-235b-a22b-thinking-2507',

        // ── Qwen3 Next ────────────────────────────────────────────
        'qwen3-next-80b-a3b-instruct',
        'qwen3-next-80b-a3b-thinking',

        // ── Qwen3.5 Series ────────────────────────────────────────
        'qwen3.5-27b',
        'qwen3.5-35b-a3b',
        'qwen3.5-122b-a10b',
        'qwen3.5-397b-a17b',
        'qwen3.5-flash',
        'qwen3.5-flash-2026-02-23',
        'qwen3.5-plus',
        'qwen3.5-plus-2026-02-15',
        'qwen3.5-plus-2026-04-20',

        // ── Qwen3.6 Series ────────────────────────────────────────
        'qwen3.6-plus-2026-04-02',
        'qwen3.6-max-preview',

        // ── Qwen3.7 Series ────────────────────────────────────────
        'qwen3.7-plus',
        'qwen3.7-max-preview',
        'qwen3.7-max-2026-05-20',

        // ── Qwen‑Coder Series ─────────────────────────────────────
        'qwen3-coder-flash',
        'qwen3-coder-flash-2025-07-28',
        'qwen3-coder-plus',
        'qwen3-coder-plus-2025-07-22',
        'qwen3-coder-plus-2025-09-23',
        'qwen3-coder-30b-a3b-instruct',
        'qwen3-coder-480b-a35b-instruct',
        'qwen3-coder-next',

        // ── Reasoning Models (QWQ / QVQ) ──────────────────────────
        'qwq-plus',
        'qvq-max-2025-03-25',
        'qvq-max-latest',

        // ── Third‑party models available via DashScope ─────────────
        'deepseek-v3.2',
        'deepseek-v4-pro',
        'deepseek-v4-flash',
        'glm-5.1',
        'kimi-k2.7-code',
    ],

    'pool_size' => 20,          // legacy, kept for reference
    'timeout' => 30,            // seconds

    'retry' => [
        'times' => 1,           // legacy – rotation logic ignores this
        'sleep_ms' => 500,
    ],

    'fireworks_fallback' => env('FIREWORKS_FALLBACK_ENABLED', true),
];