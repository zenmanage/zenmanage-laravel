<?php

declare(strict_types=1);

namespace Zenmanage\Laravel;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Zenmanage\Config\Config;
use Zenmanage\Flags\FlagManagerInterface;
use Zenmanage\Zenmanage;

/**
 * Service provider for bootstrapping Zenmanage in Laravel applications.
 *
 * Registers the Zenmanage client, flag manager, and facade into the container.
 * Publishes configuration files and sets up the facade alias.
 */
class ZenmanageServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public $bindings = [
        Contracts\Client::class => Services\DirectClient::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $configPath = __DIR__.'/../config/zenmanage.php';

        $this->publishes([$configPath => config_path('zenmanage.php')], 'config');
        $this->mergeConfigFrom($configPath, 'zenmanage');

        // Register the Zenmanage facade alias
        if (class_exists(AliasLoader::class)) {
            AliasLoader::getInstance()->alias(
                'Zenmanage',
                Facades\Zenmanage::class
            );
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Zenmanage::class, function () {
            $config = config('zenmanage');

            return new Zenmanage(
                new Config(
                    environmentToken: $config['environment_token'] ?? '',
                    cacheTtl: $config['cache_ttl'] ?? 3600,
                    cacheBackend: $config['cache_backend'] ?? 'memory',
                    cacheDirectory: $config['cache_directory'] ?? null,
                    enableUsageReporting: $config['enable_usage_reporting'] ?? false,
                    apiEndpoint: $config['api_endpoint'] ?? 'https://api.zenmanage.com',
                )
            );
        });

        $this->app->singleton(FlagManagerInterface::class, fn () => $this->app->make(Zenmanage::class)->flags());
    }
}
