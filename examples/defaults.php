<?php

declare(strict_types=1);

use Zenmanage\Flags\Context\Context;
use Zenmanage\Flags\DefaultsCollection;
use Zenmanage\Laravel\Facades\Zenmanage;

/**
 * Defaults Usage (Laravel Facade)
 *
 * Shows inline defaults and DefaultsCollection fallbacks while using the
 * Zenmanage facade inside a Laravel app.
 */

echo "=== Defaults Usage (Laravel Facade) ===\n\n";

// Inline defaults on single() calls
echo "1. Inline Defaults\n\n";

$missingString = Zenmanage::single('nonexistent-string-flag', 'hello-world');
echo "   String default: " . $missingString->asString() . "\n";

$missingNumber = Zenmanage::single('nonexistent-number-flag', 42);
echo "   Number default: " . $missingNumber->asNumber() . "\n";

$missingBoolean = Zenmanage::single('nonexistent-boolean-flag', true);
echo "   Boolean default (isEnabled): " . ($missingBoolean->isEnabled() ? 'enabled' : 'disabled') . "\n\n";

// DefaultsCollection applied to the facade instance
echo "2. Defaults Collection\n\n";

$defaults = DefaultsCollection::fromArray([
    'fallback-theme' => 'dark',
    'max-items' => 100,
    'feature-x' => false,
]);

$flagsWithDefaults = Zenmanage::withDefaults($defaults);

$themeFlag = $flagsWithDefaults->single('fallback-theme');
echo "   Fallback theme: " . $themeFlag->asString() . "\n";

$maxItemsFlag = $flagsWithDefaults->single('max-items');
echo "   Max items: " . $maxItemsFlag->asNumber() . "\n";

$featureXFlag = $flagsWithDefaults->single('feature-x');
echo "   Feature X: " . ($featureXFlag->isEnabled() ? 'enabled' : 'disabled') . "\n\n";

// Precedence between inline and collection defaults
echo "3. Precedence (Inline > Collection)\n\n";

$defaults->set('priority-flag', 'from-collection');

$priorityInline = Zenmanage::withDefaults($defaults)
    ->single('priority-flag', 'from-inline');

echo "   priority-flag with inline default: " . $priorityInline->asString() . "\n";

$priorityFromCollection = Zenmanage::withDefaults($defaults)
    ->single('priority-flag');

echo "   priority-flag from collection: " . $priorityFromCollection->asString() . "\n\n";

// Defaults with context
echo "4. Defaults with Context\n\n";

$userContext = Context::single('user', 'user-101', 'Test User');

$contextualFlag = Zenmanage::withContext($userContext)
    ->withDefaults($defaults)
    ->single('feature-per-user');

echo "   feature-per-user for user-101: " . ($contextualFlag->isEnabled() ? 'enabled' : 'disabled') . "\n\n";

echo "Examples completed!\n";
