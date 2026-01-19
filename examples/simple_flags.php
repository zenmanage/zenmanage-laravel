<?php

declare(strict_types=1);

use Zenmanage\Laravel\Facades\Zenmanage;

/*
 * Simple Flag Operations (Laravel Facade)
 *
 * Demonstrates basic flag retrieval and type-safe value access via the
 * Zenmanage facade inside a Laravel application.
 */

echo "=== Simple Flag Operations (Laravel Facade) ===\n\n";

// Boolean flags
echo "1. Boolean Flags\n\n";

$boolFlag = Zenmanage::single('example-boolean-flag');
echo '   Boolean Flag: '.($boolFlag->asBool() ? 'true' : 'false')."\n";

$enabledFlag = Zenmanage::single('example-feature-enabled');
echo '   Feature Enabled: '.($enabledFlag->isEnabled() ? 'Yes' : 'No')."\n\n";

// String flags
echo "2. String Flags\n\n";

$stringFlag = Zenmanage::single('example-string-flag');
echo "   String Flag: {$stringFlag->asString()}\n";

$variantFlag = Zenmanage::single('example-variant-flag');
echo "   Variant Flag: {$variantFlag->asString()}\n\n";

// Number flags
echo "3. Number Flags\n\n";

$numberFlag = Zenmanage::single('example-number-flag');
echo "   Number Flag: {$numberFlag->asNumber()}\n";

$limitFlag = Zenmanage::single('example-limit-flag');
echo "   Limit Flag: {$limitFlag->asNumber()}\n\n";

// Get all flags
echo "4. Retrieving All Flags\n\n";

$flags = Zenmanage::all();
echo '   Total flags: '.count($flags)."\n\n";

foreach ($flags as $flag) {
    echo "   - {$flag->getKey()} ({$flag->getType()}): ";

    if ('boolean' === $flag->getType()) {
        echo $flag->isEnabled() ? 'enabled' : 'disabled';
    } else {
        echo $flag->getValue();
    }

    echo "\n";
}

echo "\n=== Type-Safe Access ===\n\n";

$flag = Zenmanage::single('example-number-flag');

echo "   getValue(): {$flag->getValue()}\n";
echo "   asString(): {$flag->asString()}\n";
echo "   asNumber(): {$flag->asNumber()}\n";
echo '   asBool(): '.($flag->asBool() ? 'true' : 'false')."\n";
echo '   isEnabled(): '.($flag->isEnabled() ? 'true' : 'false')."\n\n";

echo "Examples completed!\n";
