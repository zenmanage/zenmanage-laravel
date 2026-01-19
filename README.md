# Zenmanage Laravel SDK

[![Build Status](https://github.com/zenmanage/zenmanage-laravel/actions/workflows/ci.yml/badge.svg)](https://github.com/zenmanage/zenmanage-laravel) [![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=zenmanage_zenmanage-laravel&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=zenmanage_zenmanage-laravel)

Add feature flags to your Laravel application in minutes. Control feature rollouts, A/B test, and manage configurations without deploying code.

## Why Zenmanage?

- 🚀 **Fast**: Rules cached locally - ~1ms evaluation time
- 🎯 **Targeted**: Roll out features to specific users, organizations, or segments
- 🛡️ **Safe**: Graceful fallbacks and error handling built-in
- 📊 **Insightful**: Automatic usage tracking (optional)
- 🧪 **Testable**: Easy to mock in tests
- 🔧 **Laravel Native**: Service provider, facade, and artisan commands included

## Installation

```bash
composer require zenmanage/zenmanage-laravel
```

**Requirements**: Laravel 11+, PHP 8.1+

The service provider will be auto-discovered. If you need to manually publish the config:

```bash
php artisan vendor:publish --provider="Zenmanage\Laravel\ZenmanageServiceProvider"
```

## Get Started in 60 Seconds

1. Get your environment token from [zenmanage.com](https://zenmanage.com)
2. Set your token in `.env`:

```env
ZENMANAGE_TOKEN=tok_your_token_here
```

3. Check a feature flag:

```php
use Zenmanage\Laravel\Facades\Zenmanage;

if (Zenmanage::single('new-dashboard')->isEnabled()) {
    return view('dashboard-v2');
}

return view('dashboard');
```

That's it! 🎉

## Configuration

The only required configuration is the Environment Token. Configuration values are set in `config/zenmanage.php`:

- `environment_token` - Your Zenmanage environment token (required)
- `cache_ttl` - Cache duration in seconds (default: 3600)
- `cache_backend` - Cache strategy: 'memory' or 'filesystem' (default: 'memory')
- `cache_directory` - Directory for filesystem cache (optional)
- `enable_usage_reporting` - Enable automatic usage tracking (default: false)
- `api_endpoint` - API endpoint URL (default: https://api.zenmanage.com)

```env
ZENMANAGE_TOKEN=tok_sample
ZENMANAGE_CACHE_BACKEND=filesystem
ZENMANAGE_CACHE_TTL=3600
ZENMANAGE_USAGE_REPORTING=false
ZENMANAGE_API_ENDPOINT=https://api.zenmanage.com
```

## Common Use Cases

### Roll Out a Feature Gradually

```php
use Zenmanage\Flags\Context\Context;
use Zenmanage\Laravel\Facades\Zenmanage;

// Check if user has access to beta features
$context = Context::single('user', $user->id, $user->name);

$betaAccess = Zenmanage::withContext($context)
    ->single('beta-program')
    ->isEnabled();

if ($betaAccess) {
    // User is in beta program
    $features = $this->getBetaFeatures();
}
```

### A/B Testing

```php
$context = Context::fromArray([
    'type' => 'user',
    'identifier' => $user->id,
    'name' => $user->name,
    'attributes' => [
        ['key' => 'country', 'values' => [['value' => $user->country]]],
    ],
]);

$variant = Zenmanage::withContext($context)
    ->single('checkout-flow')
    ->asString();

if ($variant === 'one-page') {
    return view('checkout.onepage');
}

return view('checkout.multipage');
```

### Feature Toggles by Organization

```php
$context = Context::fromArray([
    'type' => 'organization',
    'identifier' => $user->organization->id,
    'name' => $user->organization->name,
    'attributes' => [
        ['key' => 'plan', 'values' => [['value' => $user->organization->plan]]],
    ],
]);

$advancedReports = Zenmanage::withContext($context)
    ->single('advanced-reports')
    ->isEnabled();

if ($advancedReports) {
    return $this->getAdvancedReports();
}
```

### Configuration Values

```php
// Get configuration values from flags
$apiTimeout = Zenmanage::single('api-timeout', 5000)->asNumber();
$maxUploadSize = Zenmanage::single('max-upload-mb', 10)->asNumber();
$welcomeMessage = Zenmanage::single('welcome-text', 'Welcome!')->asString();
```

### Kill Switch for Problem Features

```php
// Quickly disable a problematic feature via dashboard
if (Zenmanage::single('new-payment-processor', false)->isEnabled()) {
    return $this->processWithNewSystem($payment);
}

return $this->processWithLegacySystem($payment);
```

## Working with Contexts

Contexts let you target flags to specific users, organizations, or any custom attributes. This is how you do gradual rollouts, A/B tests, and targeted features.

### Simple Context (One Attribute)

```php
use Zenmanage\Flags\Context\Context;

// Target by user ID with name
$context = Context::single('user', $user->id, $user->name);

// Target by organization
$context = Context::single('organization', $company->id, $company->name);

// Target by user with just ID
$context = Context::single('user', $user->id);
```

### Rich Context (Multiple Attributes)

```php
$context = Context::fromArray([
    'type' => 'user',
    'identifier' => $user->id,
    'name' => $user->name,
    'attributes' => [
        ['key' => 'organization', 'values' => [['value' => $user->company->slug]]],
        ['key' => 'plan', 'values' => [['value' => $user->subscription->plan]]],
        ['key' => 'role', 'values' => [['value' => $user->role]]],
        ['key' => 'country', 'values' => [['value' => $user->country]]],
    ],
]);

$premiumFeatures = Zenmanage::withContext($context)
    ->single('premium-dashboard')
    ->isEnabled();
```

**What you get:**
- `type`: Context type (user, organization, etc.)
- `identifier`: Unique identifier for targeting
- `name`: Human-readable display name
- `attributes`: Array of additional attributes for advanced targeting (plan, role, country, etc.)

**When to use contexts:**
- Rolling out to specific users (beta testers)
- Organization-based features (enterprise vs. free)
- Regional features (different countries)
- Role-based access (admins, moderators)
- Plan-based features (pro vs. basic)

## Safe Defaults - Never Break Your App

Always provide defaults for critical features. The SDK will use them if:
- Flag doesn't exist yet
- API is unreachable
- Network issues occur

### Inline Defaults (Recommended)

```php
// If 'new-checkout' doesn't exist, returns true
$enabled = Zenmanage::single('new-checkout', true)->isEnabled();

// Configuration value with fallback
$timeout = Zenmanage::single('api-timeout', 5000)->asNumber();
```

### Default Collections (For Multiple Flags)

```php
use Zenmanage\Flags\DefaultsCollection;

$defaults = DefaultsCollection::fromArray([
    'new-ui' => true,
    'max-upload-size' => 10,
    'welcome-message' => 'Welcome to our app!',
    'feature-x' => false,
]);

$flags = Zenmanage::withDefaults($defaults);

// All these will use defaults if flags don't exist
$newUI = $flags->single('new-ui')->isEnabled();
$maxSize = $flags->single('max-upload-size')->asNumber();
$message = $flags->single('welcome-message')->asString();
```

### Priority Order

When retrieving a flag, the SDK checks in this order:

1. **API Value** - If flag exists in Zenmanage
2. **Inline Default** - Value passed to `single('flag', default)`
3. **Collection Default** - From `DefaultsCollection`
4. **Exception** - If none of the above

```php
$defaults = DefaultsCollection::fromArray(['timeout' => 3000]);

// Uses API value if exists, otherwise inline (5000), then collection (3000)
$timeout = Zenmanage::withDefaults($defaults)
    ->single('timeout', 5000)
    ->asNumber();
```

## Retrieving Feature Flags

### Using the Facade (Recommended)

```php
use Zenmanage\Laravel\Facades\Zenmanage;

// Get all flags
$flags = Zenmanage::all();

// Get a single flag
$flag = Zenmanage::single('flag-key');

// With default
$flag = Zenmanage::single('flag-key', false);
```

### Using Dependency Injection

```php
use Zenmanage\Laravel\Contracts\Client;

class DashboardController extends Controller
{
    public function __construct(private Client $zenmanage) {}

    public function index()
    {
        if ($this->zenmanage->single('new-dashboard')->isEnabled()) {
            return view('dashboard-v2');
        }

        return view('dashboard');
    }
}
```

## Getting Flag Values

### All Flags

```php
$results = Zenmanage::all();

foreach ($results as $flag) {
    $key = $flag->getKey();
    $type = $flag->getType();
    
    // Get value based on type
    if ($flag->getType() === 'boolean') {
        $value = $flag->asBool();
    } elseif ($flag->getType() === 'number') {
        $value = $flag->asNumber();
    } else {
        $value = $flag->asString();
    }
}
```

### Single Flag

```php
$flag = Zenmanage::single('flag-key');

// Boolean check
if ($flag->isEnabled()) {
    // Feature is enabled
}

// Get typed values
$boolValue = $flag->asBool();
$stringValue = $flag->asString();
$numberValue = $flag->asNumber();
```

## Reporting Feature Flag Usage

When your application uses a feature flag, it can notify Zenmanage of the usage. This helps Zenmanage determine which flags are active and which may have been abandoned. Note that `single()` automatically reports usage, so you typically don't need to call this manually.

```php
use Zenmanage\Flags\Context\Context;
use Zenmanage\Laravel\Facades\Zenmanage;

// Report usage (optional - single() does this automatically)
Zenmanage::reportUsage('flag-key');

// Report with context
$context = Context::single('user', 'user-123');
Zenmanage::reportUsage('flag-key', $context);
```

## Refreshing Rules

You can manually refresh the flag rules from the API:

```php
use Zenmanage\Laravel\Facades\Zenmanage;

Zenmanage::refreshRules();
```

## Testing

Mock the Zenmanage facade in your tests:

```php
use Zenmanage\Flags\Flag;
use Zenmanage\Laravel\Facades\Zenmanage;

public function test_new_feature_for_beta_users()
{
    Zenmanage::shouldReceive('single')
        ->with('beta-feature')
        ->andReturn(new Flag(...));

    // Your test code
}
```

Or use `::fake()` to disable actual API calls:

```php
Zenmanage::fake();

// Now calls to Zenmanage won't hit the real API
```

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/zenmanage/zenmanage-laravel. This project is intended to be a safe, welcoming space for collaboration, and contributors are expected to adhere to the [Contributor Covenant](http://contributor-covenant.org) code of conduct.

## License

The library is available as open source under the terms of the [MIT License](http://opensource.org/licenses/MIT).

## Code of Conduct

Everyone interacting in the Zenmanage's code bases, issue trackers, chat rooms and mailing lists is expected to follow the [code of conduct](https://github.com/zenmanage/zenmanage-laravel/blob/master/CODE_OF_CONDUCT.md).

## What is Zenmanage?

[Zenmanage](https://zenmanage.com/) allows you to control which features and settings are enabled in your application giving you better flexibility to deploy code and release features.

Zenmanage was started in 2024 as an alternative to highly complex feature flag tools. Learn more [about us](https://zenmanage.com/).
