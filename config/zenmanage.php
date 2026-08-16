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

    /*
    |--------------------------------------------------------------------------
    | Webhook
    |--------------------------------------------------------------------------
    |
    | When enabled, the package registers a route that a Zenmanage environment
    | webhook can call to immediately refresh cached flag rules instead of
    | waiting for the cache TTL to lapse. Disabled by default — set `enabled`
    | to true and configure a webhook in your Zenmanage dashboard pointing at
    | this app's `path` to turn it on.
    |
    | `secret` should match the signing secret shown when you create the
    | webhook (prefixed `whsec_`) so incoming requests can be verified via
    | the `X-Zenmanage-Signature` header. Requests are rejected unless a
    | secret is configured.
    |
    */
    'webhook' => [
        'enabled' => env('ZENMANAGE_WEBHOOK_ENABLED', false),
        'path' => env('ZENMANAGE_WEBHOOK_PATH', 'zenmanage/webhook'),
        'secret' => env('ZENMANAGE_WEBHOOK_SECRET'),
    ],
];
