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
    private const LARAVEL_CLIENT_AGENT = 'zenmanage-laravel';

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

            $configArgs = [
                'environmentToken' => $config['environment_token'] ?? '',
                'cacheTtl' => (int) ($config['cache_ttl'] ?? 3600),
                'cacheBackend' => $config['cache_backend'] ?? 'memory',
                'cacheDirectory' => $config['cache_directory'] ?? null,
                'enableUsageReporting' => (bool) ($config['enable_usage_reporting'] ?? true),
                'apiEndpoint' => $config['api_endpoint'] ?? 'https://api.zenmanage.com',
            ];

            $sdkVersion = $this->resolveLaravelSdkVersion();
            if (is_string($sdkVersion) && $sdkVersion !== '' && $this->supportsConfigArgument('sdkVersion')) {
                $configArgs['sdkVersion'] = $sdkVersion;
            }

            if ($this->supportsConfigArgument('clientAgent')) {
                $configArgs['clientAgent'] = self::LARAVEL_CLIENT_AGENT;
            }

            return new Zenmanage(
                new Config(...$configArgs)
            );
        });

        $this->app->singleton(FlagManagerInterface::class, fn () => $this->app->make(Zenmanage::class)->flags());
    }

    private function resolveLaravelSdkVersion(): ?string
    {
        if (!class_exists(\Composer\InstalledVersions::class)) {
            return null;
        }

        try {
            if (!\Composer\InstalledVersions::isInstalled('zenmanage/zenmanage-laravel')) {
                return null;
            }

            $version = \Composer\InstalledVersions::getPrettyVersion('zenmanage/zenmanage-laravel');

            return is_string($version) && $version !== '' ? $version : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function supportsConfigArgument(string $argumentName): bool
    {
        static $supportedArguments = null;

        if ($supportedArguments === null) {
            $constructor = (new \ReflectionClass(Config::class))->getConstructor();

            if ($constructor === null) {
                $supportedArguments = [];
            } else {
                $supportedArguments = array_map(
                    static fn (\ReflectionParameter $parameter): string => $parameter->getName(),
                    $constructor->getParameters(),
                );
            }
        }

        return in_array($argumentName, $supportedArguments, true);
    }
}
