<?php

namespace Zenmanage\Laravel;

use Illuminate\Support\ServiceProvider;

class ZenmanageServiceProvider extends ServiceProvider
{
    public $bindings = [
        Contracts\Client::class => Services\DirectClient::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/zenmanage.php';

        $this->publishes([$configPath => config_path('zenmanage.php')], 'config');
        $this->mergeConfigFrom($configPath, 'zenmanage');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Zenmanage\Client::class, function() {
            return new \Zenmanage\Client(config('zenmanage'));
        });
    }
}
