# Zenmanage Laravel SDK Examples

These snippets mirror the PHP SDK examples but use the Laravel facade (`Zenmanage`). They are intended to be run inside a Laravel app that has `zenmanage/zenmanage-laravel` installed and configured.

## Prerequisites
- Laravel 11+ application with this package installed.
- `ZENMANAGE_ENVIRONMENT_TOKEN` set in your `.env` (and optional cache settings: `ZENMANAGE_CACHE_BACKEND`, `ZENMANAGE_CACHE_TTL`, `ZENMANAGE_CACHE_DIRECTORY`).
- From your app root run `php artisan config:clear` after changing env values.

## How to Run
You can execute any script via Tinker:

```bash
php artisan tinker --execute "require base_path('vendor/zenmanage/zenmanage-laravel/examples/simple_flags.php');"
php artisan tinker --execute "require base_path('vendor/zenmanage/zenmanage-laravel/examples/context_based_flags.php');"
php artisan tinker --execute "require base_path('vendor/zenmanage/zenmanage-laravel/examples/defaults.php');"
php artisan tinker --execute "require base_path('vendor/zenmanage/zenmanage-laravel/examples/caching.php');"
php artisan tinker --execute "require base_path('vendor/zenmanage/zenmanage-laravel/examples/ab_testing.php');"
php artisan tinker --execute "require base_path('vendor/zenmanage/zenmanage-laravel/examples/percentage_rollouts.php');"
```

Feel free to copy the snippets into your controllers, jobs, or tests.

## Examples

1. `simple_flags.php`
   - Basic flag retrieval and type-safe access with the facade.
2. `context_based_flags.php`
   - Apply user/organization/service contexts for rule-based evaluation.
3. `defaults.php`
   - Inline defaults and `DefaultsCollection` fallbacks.
4. `caching.php`
   - Configure cache backend/TTL and refresh rules via config/env.
5. `ab_testing.php`
   - A/B variant evaluation using a deterministic bucket attribute.
6. `percentage_rollouts.php`
   - SDK-side percentage rollouts with automatic CRC32B bucketing.
