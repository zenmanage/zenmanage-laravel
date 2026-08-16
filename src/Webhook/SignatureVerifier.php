<?php

declare(strict_types=1);

namespace Zenmanage\Laravel\Webhook;

/**
 * Verifies the HMAC-SHA256 signature Zenmanage attaches to outbound
 * environment webhook requests via the `X-Zenmanage-Signature` header,
 * formatted as `sha256=<hex>`.
 */
final class SignatureVerifier
{
    private const SIGNATURE_PREFIX = 'sha256=';

    /**
     * Verify that the given signature header matches the payload when
     * signed with the webhook secret.
     *
     * @param string $payload         The raw request body, exactly as received
     * @param string $signatureHeader The value of the `X-Zenmanage-Signature` header
     * @param string $secret          The webhook's signing secret (`whsec_...`)
     */
    public static function verify(string $payload, string $signatureHeader, string $secret): bool
    {
        if ('' === $secret) {
            return false;
        }

        if (false === str_starts_with($signatureHeader, self::SIGNATURE_PREFIX)) {
            return false;
        }

        $provided = substr($signatureHeader, strlen(self::SIGNATURE_PREFIX));
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $provided);
    }
}
