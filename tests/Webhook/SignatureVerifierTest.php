<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Zenmanage\Laravel\Webhook\SignatureVerifier;

/**
 * @internal
 *
 * @covers \Zenmanage\Laravel\Webhook\SignatureVerifier
 */
class SignatureVerifierTest extends TestCase
{
    private const SECRET = 'whsec_test_secret';

    public function testReturnsTrueForAValidSignature(): void
    {
        $payload = '{"action":"target_rule.updated"}';
        $signature = 'sha256='.hash_hmac('sha256', $payload, self::SECRET);

        $this->assertTrue(SignatureVerifier::verify($payload, $signature, self::SECRET));
    }

    public function testReturnsFalseWhenSignatureDoesNotMatchThePayload(): void
    {
        $signature = 'sha256='.hash_hmac('sha256', 'tampered payload', self::SECRET);

        $this->assertFalse(SignatureVerifier::verify('{"action":"target_rule.updated"}', $signature, self::SECRET));
    }

    public function testReturnsFalseWhenSignedWithADifferentSecret(): void
    {
        $payload = '{"action":"target_rule.updated"}';
        $signature = 'sha256='.hash_hmac('sha256', $payload, 'whsec_wrong_secret');

        $this->assertFalse(SignatureVerifier::verify($payload, $signature, self::SECRET));
    }

    public function testReturnsFalseWhenSignatureIsMissingTheAlgorithmPrefix(): void
    {
        $payload = '{"action":"target_rule.updated"}';
        $signature = hash_hmac('sha256', $payload, self::SECRET);

        $this->assertFalse(SignatureVerifier::verify($payload, $signature, self::SECRET));
    }

    public function testReturnsFalseWhenSignatureHeaderIsEmpty(): void
    {
        $this->assertFalse(SignatureVerifier::verify('{"action":"target_rule.updated"}', '', self::SECRET));
    }

    public function testReturnsFalseWhenSecretIsEmpty(): void
    {
        $payload = '{"action":"target_rule.updated"}';
        $signature = 'sha256='.hash_hmac('sha256', $payload, self::SECRET);

        $this->assertFalse(SignatureVerifier::verify($payload, $signature, ''));
    }
}
