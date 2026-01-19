<?php

declare(strict_types=1);

namespace Zenmanage\Laravel\Services;

use Zenmanage\Flags\Context\Context;
use Zenmanage\Flags\DefaultsCollection;
use Zenmanage\Flags\Flag;
use Zenmanage\Flags\FlagManagerInterface;
use Zenmanage\Laravel\Contracts\Client;

/**
 * Direct implementation of the Client contract.
 *
 * Delegates to the Zenmanage FlagManager for flag evaluation.
 * Provides immutable operations via cloning.
 */
class DirectClient implements Client
{
    public function __construct(
        private FlagManagerInterface $flagManager
    ) {}

    /**
     * Set the evaluation context for flag matching.
     *
     * @param Context $context The context for evaluating flags
     *
     * @return self A new client instance with the context applied
     */
    public function withContext(Context $context): Client
    {
        $clone = clone $this;
        $clone->flagManager = $this->flagManager->withContext($context);

        return $clone;
    }

    /**
     * Set default values for flags when they are not found.
     *
     * @param DefaultsCollection $defaults Collection of default flag values
     *
     * @return self A new client instance with defaults applied
     */
    public function withDefaults(DefaultsCollection $defaults): Client
    {
        $clone = clone $this;
        $clone->flagManager = $this->flagManager->withDefaults($defaults);

        return $clone;
    }

    /**
     * @return Flag[]
     */
    public function all(): array
    {
        return $this->flagManager->all();
    }

    /**
     * Report that a flag was evaluated.
     *
     * @param string       $key     The flag key that was evaluated
     * @param null|Context $context Optional context for tracking
     */
    public function reportUsage(string $key, ?Context $context = null): void
    {
        $this->flagManager->reportUsage($key, $context);
    }

    /**
     * Get a single feature flag by key.
     *
     * @param string $key     The flag key to retrieve
     * @param mixed  $default Optional default value if not found
     *
     * @return Flag The evaluated feature flag
     */
    public function single(string $key, mixed $default = null): Flag
    {
        return $this->flagManager->single($key, $default);
    }

    /**
     * Refresh flag rules from the API.
     */
    public function refreshRules(): void
    {
        $this->flagManager->refreshRules();
    }
}
