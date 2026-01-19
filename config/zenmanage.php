<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Environment Token
    |--------------------------------------------------------------------------
    |
    | Your Zenmanage environment token. Get this from your Zenmanage dashboard.
    |
    */
    'environment_token' => env('ZENMANAGE_ENVIRONMENT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) to cache feature flag rules. Default is 3600 (1 hour).
    |
    */
    'cache_ttl' => env('ZENMANAGE_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Cache Backend
    |--------------------------------------------------------------------------
    |
    | The caching strategy to use. Options: 'memory', 'filesystem', 'null'
    | - memory: In-memory cache (default, per-request)
    | - filesystem: Cache to disk (recommended for production)
    | - null: No caching
    |
    */
    'cache_backend' => env('ZENMANAGE_CACHE_BACKEND', 'memory'),

    /*
    |--------------------------------------------------------------------------
    | Cache Directory
    |--------------------------------------------------------------------------
    |
    | Directory for filesystem cache. Required if cache_backend is 'filesystem'.
    | Defaults to storage/framework/cache/zenmanage
    |
    */
    'cache_directory' => env('ZENMANAGE_CACHE_DIRECTORY', storage_path('framework/cache/zenmanage')),

    /*
    |--------------------------------------------------------------------------
    | Usage Reporting
    |--------------------------------------------------------------------------
    |
    | Enable automatic usage tracking to see which flags are being used in production.
    |
    */
    'enable_usage_reporting' => env('ZENMANAGE_ENABLE_USAGE_REPORTING', true),

    /*
    |--------------------------------------------------------------------------
    | API Endpoint
    |--------------------------------------------------------------------------
    |
    | The Zenmanage API endpoint. You typically don't need to change this.
    |
    */
    'api_endpoint' => env('ZENMANAGE_API_ENDPOINT', 'https://api.zenmanage.com'),
];
