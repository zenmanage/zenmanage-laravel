<?php

declare(strict_types=1);

namespace Zenmanage\Laravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Zenmanage\Laravel\Contracts\Client;
use Zenmanage\Laravel\Webhook\SignatureVerifier;

/**
 * Handles incoming Zenmanage environment webhook requests by refreshing
 * cached flag rules, so changes propagate immediately instead of waiting
 * for the cache TTL to lapse.
 */
class WebhookController
{
    public function __construct(
        private Client $client,
        private string $secret
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if ('' === $this->secret) {
            return new JsonResponse(['message' => 'Webhook secret is not configured.'], 500);
        }

        $signatureHeader = $request->header('X-Zenmanage-Signature', '');
        $signature = is_string($signatureHeader) ? $signatureHeader : '';

        if (false === SignatureVerifier::verify((string) $request->getContent(), $signature, $this->secret)) {
            return new JsonResponse(['message' => 'Invalid signature.'], 401);
        }

        $this->client->refreshRules();

        return new JsonResponse(['message' => 'Flag rules refreshed.'], 200);
    }
}
