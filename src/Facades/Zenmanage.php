<?php

declare(strict_types=1);

namespace Zenmanage\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Zenmanage\Laravel\Contracts\Client;

/**
 * Zenmanage facade for convenient static access to flag operations.
 *
 * Provides a clean static API for checking feature flags throughout the application.
 *
 * Example:
 *     Zenmanage::single('new-feature')->isEnabled()
 *     Zenmanage::withContext($context)->single('premium-feature')->isEnabled()
 *
 * @method static Client                  withContext(\Zenmanage\Flags\Context\Context $context)
 * @method static Client                  withDefaults(\Zenmanage\Flags\DefaultsCollection $defaults)
 * @method static \Zenmanage\Flags\Flag[] all()
 * @method static void                    reportUsage(string $key, ?\Zenmanage\Flags\Context\Context $context = null)
 * @method static \Zenmanage\Flags\Flag   single(string $key, mixed $default = null)
 * @method static void                    refreshRules()
 *
 * @see Client
 */
class Zenmanage extends Facade
{
    /**
     * Get the binding name for the facade.
     *
     * @return string The container binding name for the Client contract
     */
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
