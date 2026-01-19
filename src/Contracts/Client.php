<?php

declare(strict_types=1);

namespace Zenmanage\Laravel\Contracts;

use Zenmanage\Exception\EvaluationException;
use Zenmanage\Flags\Context\Context;
use Zenmanage\Flags\DefaultsCollection;
use Zenmanage\Flags\Flag;

/**
 * Client contract for interacting with Zenmanage feature flags.
 *
 * Provides a fluent interface for evaluating flags with optional context
 * and default values.
 */
interface Client
{
    /**
     * Set the evaluation context for flag matching.
     *
     * Returns a new instance to maintain immutability.
     *
     * @param Context $context The context containing user/organization/custom attributes
     *
     * @return self A new client instance with the context applied
     */
    public function withContext(Context $context): Client;

    /**
     * Set default values for flags when they are not found.
     *
     * Returns a new instance to maintain immutability.
     *
     * @param DefaultsCollection $defaults Collection of flag key => default value pairs
     *
     * @return self A new client instance with defaults applied
     */
    public function withDefaults(DefaultsCollection $defaults): Client;

    /**
     * Get all available feature flags.
     *
     * @return Flag[] Array of all flags evaluated in the current context
     */
    public function all(): array;

    /**
     * Report that a flag was evaluated.
     *
     * Notifies Zenmanage of flag usage for analytics and activity tracking.
     *
     * @param string       $key     The flag key that was evaluated
     * @param null|Context $context Optional context for tracking usage patterns
     */
    public function reportUsage(string $key, ?Context $context = null): void;

    /**
     * Get a single feature flag by key.
     *
     * @param string $key     The flag key to retrieve
     * @param mixed  $default Optional default value if the flag is not found
     *
     * @return Flag The feature flag, evaluated in the current context
     *
     * @throws EvaluationException If flag not found and no default provided
     */
    public function single(string $key, mixed $default = null): Flag;

    /**
     * Refresh flag rules from the API.
     *
     * Forces a fresh fetch of rules from the Zenmanage API and updates the cache.
     */
    public function refreshRules(): void;
}
