<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Facade as LaravelFacade;
use Zenmanage\Flags\FlagManagerInterface;
use Zenmanage\Laravel\Facades\Zenmanage;
use Zenmanage\Zenmanage as CoreZenmanage;

/**
 * Caching Setup Examples (Laravel Facade)
 *
 * Demonstrates how to configure cache backends/TTL through config/env and
 * refresh rules while using the Zenmanage facade. Run inside a Laravel app.
 */

echo "=== Caching Setup Examples (Laravel Facade) ===\n\n";

$token = (string) config('zenmanage.environment_token', 'tok_your_environment_token_here');

/**
 * Forget cached singletons so config changes take effect.
 */
function resetZenmanageBindings(): void
{
    app()->forgetInstance(CoreZenmanage::class);
    app()->forgetInstance(FlagManagerInterface::class);
    LaravelFacade::clearResolvedInstance('Zenmanage');
}

// Example 1: Memory cache (default)
echo "1. Memory Cache (default)\n\n";

config([
    'zenmanage.environment_token' => $token,
    'zenmanage.cache_backend' => 'memory',
    'zenmanage.cache_ttl' => 600,
    'zenmanage.cache_directory' => storage_path('framework/cache/zenmanage'),
]);

resetZenmanageBindings();

$zenmanageMemory = Zenmanage::all();
echo "   Loaded " . count($zenmanageMemory) . " flags using in-memory cache (TTL 600s).\n\n";

// Example 2: Filesystem cache
echo "2. Filesystem Cache\n\n";

$cacheDir = storage_path('framework/cache/zenmanage/examples');

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

config([
    'zenmanage.environment_token' => $token,
    'zenmanage.cache_backend' => 'filesystem',
    'zenmanage.cache_directory' => $cacheDir,
    'zenmanage.cache_ttl' => 3600,
]);

resetZenmanageBindings();

$fsFlags = Zenmanage::all();
$files = glob($cacheDir . '/*.cache');
$filesCount = is_array($files) ? count($files) : 0;

echo "   Cache directory: {$cacheDir}\n";
echo "   Loaded " . count($fsFlags) . " flags and found {$filesCount} cache file(s).\n\n";

// Example 3: Null cache (disable caching)
echo "3. Null Cache (disabled)\n\n";

config([
    'zenmanage.environment_token' => $token,
    'zenmanage.cache_backend' => 'null',
    'zenmanage.cache_directory' => null,
    'zenmanage.cache_ttl' => 0,
]);

resetZenmanageBindings();

Zenmanage::refreshRules();
$nullFlags = Zenmanage::all();

echo "   Loaded " . count($nullFlags) . " flags with caching disabled.\n\n";

// Example 4: From environment variables
echo "4. Environment-Based Configuration\n\n";

putenv('ZENMANAGE_ENVIRONMENT_TOKEN=' . $token);
putenv('ZENMANAGE_CACHE_BACKEND=filesystem');
putenv('ZENMANAGE_CACHE_TTL=120');
putenv('ZENMANAGE_CACHE_DIRECTORY=' . $cacheDir);

config([
    'zenmanage.environment_token' => env('ZENMANAGE_ENVIRONMENT_TOKEN'),
    'zenmanage.cache_backend' => env('ZENMANAGE_CACHE_BACKEND', 'memory'),
    'zenmanage.cache_ttl' => (int) env('ZENMANAGE_CACHE_TTL', 3600),
    'zenmanage.cache_directory' => env('ZENMANAGE_CACHE_DIRECTORY', $cacheDir),
]);

resetZenmanageBindings();

$envFlags = Zenmanage::all();
$envFiles = glob($cacheDir . '/*.cache');
$envFilesCount = is_array($envFiles) ? count($envFiles) : 0;

echo "   Read config from ZENMANAGE_* environment variables.\n";
echo "   Loaded " . count($envFlags) . " flags; cache files present: {$envFilesCount}.\n\n";

echo "Examples completed!\n";
