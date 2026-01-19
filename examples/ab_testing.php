<?php

declare(strict_types=1);

use Zenmanage\Flags\Context\Attribute;
use Zenmanage\Flags\Context\Context;
use Zenmanage\Laravel\Facades\Zenmanage;

/**
 * A/B Testing with Flags + Context (Laravel Facade)
 *
 * Evaluates a string variant flag using user context and a deterministic
 * bucket attribute. Designed to run inside a Laravel app (e.g., via Tinker).
 */

echo "=== A/B Testing Example (Laravel Facade) ===\n\n";

/**
 * Compute a deterministic bucket (0-99) from a stable identifier.
 */
function abBucket(string $identifier): int
{
    $hash = crc32($identifier);
    return (int) ($hash % 100);
}

/**
 * Evaluate a variant flag for a given user.
 */
function evaluateVariant(string $userId, string $userName): void
{
    $bucket = abBucket($userId);

    $context = new Context(
        type: 'user',
        name: $userName,
        identifier: $userId,
        attributes: [
            new Attribute('ab_bucket', [(string) $bucket]),
            // Add more targeting signals if needed, e.g. country/plan
        ]
    );

    $variantFlag = Zenmanage::withContext($context)
        ->single('landing-page-variant', 'control');

    $variant = $variantFlag->asString();

    echo "User {$userId} ({$userName})\n";
    echo "   Bucket: {$bucket}\n";
    echo "   Variant: {$variant}\n\n";
}

// Try a few users to see deterministic bucket assignment
evaluateVariant('user-1001', 'Alice');
evaluateVariant('user-1002', 'Bob');
evaluateVariant('user-1003', 'Charlie');

echo "Examples completed!\n";
