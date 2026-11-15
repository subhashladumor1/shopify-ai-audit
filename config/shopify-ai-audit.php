<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Guardian Master Switch
    |--------------------------------------------------------------------------
    |
    | When set to false, all background monitoring, automated auditing,
    | and scheduled telemetry collectors will be disabled.
    |
    */
    'enabled' => env('GUARDIAN_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Database Table Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix applied to all package tables (e.g. guardian_audits).
    |
    */
    'table_prefix' => env('GUARDIAN_TABLE_PREFIX', 'guardian_'),

    /*
    |--------------------------------------------------------------------------
    | Developer Dashboard
    |--------------------------------------------------------------------------
    |
    | The native Blade dashboard interface configuration.
    |
    */
    'dashboard' => [
        'enabled' => env('GUARDIAN_DASHBOARD_ENABLED', true),
        'path' => env('GUARDIAN_DASHBOARD_PATH', 'guardian'),
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | REST API Interface
    |--------------------------------------------------------------------------
    |
    | Optional REST API endpoints for external integrations and CI/CD pipelines.
    |
    */
    'api' => [
        'enabled' => env('GUARDIAN_API_ENABLED', true),
        'prefix' => env('GUARDIAN_API_PREFIX', 'api/guardian'),
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Core Audit Engine Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for project discovery, scanner rules, and execution thresholds.
    |
    */
    'audit' => [
        'default_mode' => 'read_only',
        'enabled_scanners' => [
            'shopify_security' => true,
            'shopify_api' => true,
            'shopify_app_store' => true,
            'shopify_governance' => true,
            'laravel_security' => true,
            'php_security' => true,
            'frontend' => true,
            'database' => true,
            'cloudflare' => true,
            'performance' => true,
            'seo' => true,
        ],
        'excluded_paths' => [
            'vendor',
            'node_modules',
            'storage',
            '.git',
            'tests/Fixtures',
        ],
        'max_file_size' => 1048576, // 1 MB
        'timeout' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Shopify Platform Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Rules and parameters specifically targeting Shopify apps, extensions & themes.
    |
    */
    'shopify' => [
        'enabled' => true,
        'app_type' => env('SHOPIFY_APP_TYPE', 'auto'), // auto, public, custom, embedded, headless
        'target_api_version' => env('SHOPIFY_API_VERSION', '2025-01'),
        'security_checks' => true,
        'api_compatibility' => true,
        'app_store_audit' => true,
        'partner_governance' => true,
        'privacy_checks' => true,
        'extension_checks' => true,
        'webhook_tolerance_seconds' => 300,
        'enforce_frame_ancestors' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Platform Configuration
    |--------------------------------------------------------------------------
    |
    | Optional AI augmentation layer. Kept strictly optional for deterministic audits.
    |
    */
    'ai' => [
        'enabled' => env('GUARDIAN_AI_ENABLED', false),
        'driver' => env('GUARDIAN_AI_DRIVER', 'native'), // native, laravel_ai_sdk
        'default_provider' => env('GUARDIAN_AI_PROVIDER', 'openai'), // openai, anthropic, gemini, ollama
        'require_guard' => env('GUARDIAN_AI_REQUIRE_GUARD', true),
        'max_iterations' => 10,
        'max_tool_calls' => 20,
        'max_execution_seconds' => 120,
        'daily_budget_usd' => env('GUARDIAN_AI_DAILY_BUDGET_USD', 10.00),
        'monthly_budget_usd' => env('GUARDIAN_AI_MONTHLY_BUDGET_USD', 100.00),
        'providers' => [
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
                'model' => env('OPENAI_MODEL', 'gpt-4o'),
                'base_url' => env('OPENAI_BASE_URL'),
            ],
            'anthropic' => [
                'api_key' => env('ANTHROPIC_API_KEY'),
                'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
            ],
            'gemini' => [
                'api_key' => env('GEMINI_API_KEY'),
                'model' => env('GEMINI_MODEL', 'gemini-1.5-pro'),
            ],
            'ollama' => [
                'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
                'model' => env('OLLAMA_MODEL', 'qwen2.5-coder:14b'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Observability & Monitoring Settings
    |--------------------------------------------------------------------------
    |
    | Telescope/Pulse-inspired collectors for requests, queries, APIs & exceptions.
    |
    */
    'monitoring' => [
        'enabled' => env('GUARDIAN_MONITORING_ENABLED', true),
        'sampling' => (float) env('GUARDIAN_MONITORING_SAMPLING', 1.0),
        'redact_sensitive_data' => true,
        'retention_days' => 7,
        'collectors' => [
            'requests' => true,
            'queries' => true,
            'api_calls' => true,
            'exceptions' => true,
            'jobs' => true,
            'ai_executions' => true,
        ],
        'slow_query_threshold_ms' => 100,
        'shopify_quota_warning_threshold' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Remediation Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Controls automated patch proposals and verification runs.
    |
    */
    'remediation' => [
        'enabled' => true,
        'auto_apply' => false,
        'require_approval' => true,
        'storage_path' => storage_path('guardian/patches'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reports Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Export settings for PDF, HTML, JSON, Markdown, and SARIF 2.1.0 formats.
    |
    */
    'reports' => [
        'default_format' => 'html',
        'storage_path' => storage_path('guardian/reports'),
    ],

];
