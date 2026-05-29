<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Zenmanage\Flags\Context\Attribute;
use Zenmanage\Flags\Context\Context;
use Zenmanage\Flags\DefaultsCollection;
use Zenmanage\Flags\Flag;
use Zenmanage\Flags\FlagManagerInterface;
use Zenmanage\Flags\Target;
use Zenmanage\Laravel\Services\DirectClient;
use Zenmanage\Rules\RuleValue;

/**
 * @internal
 *
 * @covers \Zenmanage\Laravel\Services\DirectClient
 */
class DirectClientTest extends TestCase
{
    private FlagManagerInterface $flagManagerMock;
    private DirectClient $client;

    protected function setUp(): void
    {
        $this->flagManagerMock = $this->createMock(FlagManagerInterface::class);
        $this->client = new DirectClient($this->flagManagerMock);
    }

    public function testWithContextReturnsNewInstance(): void
    {
        $context = Context::single('user', 'test-id', 'Test User');
        $newFlagManagerMock = $this->createMock(FlagManagerInterface::class);

        $this->flagManagerMock->expects($this->once())
            ->method('withContext')
            ->with($context)
            ->willReturn($newFlagManagerMock)
        ;

        $result = $this->client->withContext($context);
        $this->assertInstanceOf(DirectClient::class, $result);
        $this->assertNotSame($this->client, $result);
    }

    public function testWithDefaultsReturnsNewInstance(): void
    {
        $defaults = new DefaultsCollection();
        $defaults->set('test-flag', true);

        $newFlagManagerMock = $this->createMock(FlagManagerInterface::class);

        $this->flagManagerMock->expects($this->once())
            ->method('withDefaults')
            ->with($defaults)
            ->willReturn($newFlagManagerMock)
        ;

        $result = $this->client->withDefaults($defaults);
        $this->assertInstanceOf(DirectClient::class, $result);
        $this->assertNotSame($this->client, $result);
    }

    public function testAllReturnsArray(): void
    {
        $expected = [];
        $this->flagManagerMock->expects($this->once())
            ->method('all')
            ->willReturn($expected)
        ;

        $result = $this->client->all();
        $this->assertSame($expected, $result);
    }

    public function testReportUsageCallsFlagManagerReportUsage(): void
    {
        $key = 'test_key';
        $context = Context::single('user', 'test-id', 'Test User');

        $this->flagManagerMock->expects($this->once())
            ->method('reportUsage')
            ->with($key, $context)
        ;

        $this->client->reportUsage($key, $context);
        $this->assertTrue(true); // If no exception, test passes
    }

    public function testSingleReturnsFlag(): void
    {
        $key = 'test_key';
        $default = false;

        // Create a real flag instance with proper objects
        $ruleValue = new RuleValue('1.0', ['boolean' => true]);
        $target = new Target(
            version: '1.0',
            expiredAt: null,
            publishedAt: null,
            scheduledAt: null,
            value: $ruleValue
        );

        $flag = new Flag(
            version: '1.0',
            type: 'boolean',
            key: 'test-flag',
            name: 'Test Flag',
            target: $target,
            rules: []
        );

        $this->flagManagerMock->expects($this->once())
            ->method('single')
            ->with($key, $default)
            ->willReturn($flag)
        ;

        $result = $this->client->single($key, $default);
        $this->assertInstanceOf(Flag::class, $result);
    }

    public function testRefreshRulesCallsFlagManagerRefreshRules(): void
    {
        $this->flagManagerMock->expects($this->once())
            ->method('refreshRules')
        ;

        $this->client->refreshRules();
        $this->assertTrue(true); // If no exception, test passes
    }

    // -------------------------------------------------------------------------
    // Context fixtures
    // -------------------------------------------------------------------------

    private function c1(): Context
    {
        return new Context('user', 'Alice US Free', 'user-us-free', [
            new Attribute('country', ['US']),
            new Attribute('plan', ['free']),
            new Attribute('age', ['25']),
            new Attribute('email', ['alice@acme.com']),
            new Attribute('tier', ['1']),
            new Attribute('tags', ['alpha', 'beta']),
        ]);
    }

    private function c2(): Context
    {
        return new Context('user', 'Bob CA Pro', 'user-ca-pro', [
            new Attribute('country', ['CA']),
            new Attribute('plan', ['pro']),
            new Attribute('age', ['42']),
            new Attribute('email', ['bob@acme.ca']),
            new Attribute('tier', ['3']),
            new Attribute('tags', ['beta', 'gamma']),
        ]);
    }

    private function c5(): Context
    {
        return new Context('user', 'Shared User', 'shared-123', [
            new Attribute('country', ['US']),
        ]);
    }

    private function c6(): Context
    {
        return new Context('organization', 'Shared Org', 'shared-123', [
            new Attribute('country', ['US']),
        ]);
    }

    // -------------------------------------------------------------------------
    // Flag factory helpers
    // -------------------------------------------------------------------------

    private function makeBoolFlag(string $key, bool $value): Flag
    {
        $ruleValue = new RuleValue('v1', ['boolean' => $value]);
        $target = new Target('tar_1', null, null, null, $ruleValue);

        return new Flag('fla_1', 'boolean', $key, $key, $target, []);
    }

    private function makeStringFlag(string $key, string $value): Flag
    {
        $ruleValue = new RuleValue('v1', ['string' => $value]);
        $target = new Target('tar_1', null, null, null, $ruleValue);

        return new Flag('fla_1', 'string', $key, $key, $target, []);
    }

    private function makeNumberFlag(string $key, int|float $value): Flag
    {
        $ruleValue = new RuleValue('v1', ['number' => $value]);
        $target = new Target('tar_1', null, null, null, $ruleValue);

        return new Flag('fla_1', 'number', $key, $key, $target, []);
    }

    // -------------------------------------------------------------------------
    // Helper: expect withContext()->single()
    // -------------------------------------------------------------------------

    private function expectContextThenSingle(Context $context, string $key, mixed $default, Flag $flag): void
    {
        $contextManager = $this->createMock(FlagManagerInterface::class);
        $contextManager->expects($this->once())
            ->method('single')
            ->with($key, $default)
            ->willReturn($flag)
        ;

        $this->flagManagerMock->expects($this->once())
            ->method('withContext')
            ->with($context)
            ->willReturn($contextManager)
        ;
    }

    // =========================================================================
    // Static value passthrough (bool false, string, number)
    // =========================================================================

    public function testSinglePassesThroughBoolFalse(): void
    {
        $this->flagManagerMock->expects($this->once())
            ->method('single')
            ->with('parity-bool-off', false)
            ->willReturn($this->makeBoolFlag('parity-bool-off', false))
        ;

        $flag = $this->client->single('parity-bool-off', false);

        $this->assertFalse($flag->asBool());
    }

    public function testSinglePassesThroughStringValue(): void
    {
        $this->flagManagerMock->expects($this->once())
            ->method('single')
            ->with('parity-string-mode', '')
            ->willReturn($this->makeStringFlag('parity-string-mode', 'control'))
        ;

        $flag = $this->client->single('parity-string-mode', '');

        $this->assertSame('control', $flag->asString());
    }

    public function testSinglePassesThroughNumberValue(): void
    {
        $this->flagManagerMock->expects($this->once())
            ->method('single')
            ->with('parity-number-timeout', 0)
            ->willReturn($this->makeNumberFlag('parity-number-timeout', 1500))
        ;

        $flag = $this->client->single('parity-number-timeout', 0);

        $this->assertSame(1500.0, (float) $flag->asNumber());
    }

    // =========================================================================
    // Default fallback
    // =========================================================================

    public function testSinglePassesThroughDefaultValue(): void
    {
        $this->flagManagerMock->expects($this->once())
            ->method('single')
            ->with('parity-missing-inline-default', false)
            ->willReturn($this->makeBoolFlag('parity-missing-inline-default', false))
        ;

        $flag = $this->client->single('parity-missing-inline-default', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithDefaultsChainDelegatesSingle(): void
    {
        $defaults = new DefaultsCollection();
        $defaults->set('parity-missing-defaults-collection', true);

        $defaultsManager = $this->createMock(FlagManagerInterface::class);
        $defaultsManager->expects($this->once())
            ->method('single')
            ->with('parity-missing-defaults-collection', null)
            ->willReturn($this->makeBoolFlag('parity-missing-defaults-collection', true))
        ;

        $this->flagManagerMock->expects($this->once())
            ->method('withDefaults')
            ->with($defaults)
            ->willReturn($defaultsManager)
        ;

        $flag = $this->client->withDefaults($defaults)->single('parity-missing-defaults-collection');

        $this->assertTrue($flag->asBool());
    }

    // =========================================================================
    // Context / rule delegation
    // =========================================================================

    public function testWithContextDelegatesContextEqualsPositive(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-ctx-equals-user', false, $this->makeBoolFlag('parity-ctx-equals-user', true));

        $flag = $this->client->withContext($c1)->single('parity-ctx-equals-user', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesContextEqualsNegative(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-ctx-equals-user', false, $this->makeBoolFlag('parity-ctx-equals-user', false));

        $flag = $this->client->withContext($c2)->single('parity-ctx-equals-user', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithContextDelegatesTypeStrictnessUserReturnsFalse(): void
    {
        $c5 = $this->c5();
        $this->expectContextThenSingle($c5, 'parity-ctx-type-strict', false, $this->makeBoolFlag('parity-ctx-type-strict', false));

        $flag = $this->client->withContext($c5)->single('parity-ctx-type-strict', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithContextDelegatesTypeStrictnessOrgReturnsTrue(): void
    {
        $c6 = $this->c6();
        $this->expectContextThenSingle($c6, 'parity-ctx-type-strict', false, $this->makeBoolFlag('parity-ctx-type-strict', true));

        $flag = $this->client->withContext($c6)->single('parity-ctx-type-strict', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesAttributeEqualsPositive(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-attr-country-equals', false, $this->makeBoolFlag('parity-attr-country-equals', true));

        $flag = $this->client->withContext($c1)->single('parity-attr-country-equals', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesAttributeEqualsNegative(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-attr-country-equals', false, $this->makeBoolFlag('parity-attr-country-equals', false));

        $flag = $this->client->withContext($c2)->single('parity-attr-country-equals', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithContextDelegatesAttributeInListNegative(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-attr-plan-in', false, $this->makeBoolFlag('parity-attr-plan-in', false));

        $flag = $this->client->withContext($c1)->single('parity-attr-plan-in', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithContextDelegatesAttributeInListPositive(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-attr-plan-in', false, $this->makeBoolFlag('parity-attr-plan-in', true));

        $flag = $this->client->withContext($c2)->single('parity-attr-plan-in', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesAttributeContainsPositive(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-attr-tags-contains', false, $this->makeBoolFlag('parity-attr-tags-contains', true));

        $flag = $this->client->withContext($c1)->single('parity-attr-tags-contains', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesAttributeEndsWithPositive(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-attr-email-suffix', false, $this->makeBoolFlag('parity-attr-email-suffix', true));

        $flag = $this->client->withContext($c1)->single('parity-attr-email-suffix', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesAttributeEndsWithNegative(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-attr-email-suffix', false, $this->makeBoolFlag('parity-attr-email-suffix', false));

        $flag = $this->client->withContext($c2)->single('parity-attr-email-suffix', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithContextDelegatesNumericGteNegative(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-attr-tier-gte', false, $this->makeBoolFlag('parity-attr-tier-gte', false));

        $flag = $this->client->withContext($c1)->single('parity-attr-tier-gte', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithContextDelegatesNumericGtePositive(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-attr-tier-gte', false, $this->makeBoolFlag('parity-attr-tier-gte', true));

        $flag = $this->client->withContext($c2)->single('parity-attr-tier-gte', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesFirstMatchWins(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-first-match-wins', 'fallback', $this->makeStringFlag('parity-first-match-wins', 'us-first'));

        $flag = $this->client->withContext($c1)->single('parity-first-match-wins', 'fallback');

        $this->assertSame('us-first', $flag->asString());
    }

    public function testWithContextDelegatesVariantPositive(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-variant-checkout', 'control', $this->makeStringFlag('parity-variant-checkout', 'treatment-us'));

        $flag = $this->client->withContext($c1)->single('parity-variant-checkout', 'control');

        $this->assertSame('treatment-us', $flag->asString());
    }

    public function testWithContextDelegatesVariantAlternate(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-variant-checkout', 'control', $this->makeStringFlag('parity-variant-checkout', 'treatment-pro'));

        $flag = $this->client->withContext($c2)->single('parity-variant-checkout', 'control');

        $this->assertSame('treatment-pro', $flag->asString());
    }

    // =========================================================================
    // all() catalog passthrough
    // =========================================================================

    public function testAllPassesThroughFlagKeys(): void
    {
        $keys = ['parity-bool-on', 'parity-bool-off', 'parity-string-mode'];
        $catalogFlags = array_map(fn (string $k) => $this->makeBoolFlag($k, false), $keys);

        $this->flagManagerMock->expects($this->once())
            ->method('all')
            ->willReturn($catalogFlags)
        ;

        $allFlags = $this->client->all();
        $returnedKeys = array_map(fn (Flag $f) => $f->getKey(), $allFlags);

        $this->assertSame($keys, $returnedKeys);
    }

    // =========================================================================
    // Cache behavior: refreshRules flips evaluated value
    // =========================================================================

    public function testRefreshRulesFlipsEvaluatedValue(): void
    {
        $callCount = 0;

        $this->flagManagerMock->expects($this->exactly(3))
            ->method('single')
            ->with('parity-cache-flip', false)
            ->willReturnCallback(function () use (&$callCount): Flag {
                ++$callCount;

                if ($callCount <= 2) {
                    return $this->makeBoolFlag('parity-cache-flip', false);
                }

                return $this->makeBoolFlag('parity-cache-flip', true);
            })
        ;

        $this->flagManagerMock->expects($this->once())
            ->method('refreshRules')
        ;

        $flag = $this->client->single('parity-cache-flip', false);
        $this->assertFalse($flag->asBool());

        $flag = $this->client->single('parity-cache-flip', false);
        $this->assertFalse($flag->asBool());

        $this->client->refreshRules();
        $flag = $this->client->single('parity-cache-flip', false);
        $this->assertTrue($flag->asBool());
    }

    // =========================================================================
    // Usage reporting without context
    // =========================================================================

    public function testReportUsageWithoutContext(): void
    {
        $this->flagManagerMock->expects($this->once())
            ->method('reportUsage')
            ->with('parity-bool-on', null)
        ;

        $this->client->reportUsage('parity-bool-on', null);
        $this->addToAssertionCount(1);
    }

    // =========================================================================
    // Negated / additional operators
    // =========================================================================

    public function testWithContextDelegatesAttributeNotEqualsNegative(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-attr-not-equals', false, $this->makeBoolFlag('parity-attr-not-equals', false));

        $flag = $this->client->withContext($c1)->single('parity-attr-not-equals', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithContextDelegatesAttributeNotEqualsPositive(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-attr-not-equals', false, $this->makeBoolFlag('parity-attr-not-equals', true));

        $flag = $this->client->withContext($c2)->single('parity-attr-not-equals', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesAttributeNotContainsNegative(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-attr-not-contains', false, $this->makeBoolFlag('parity-attr-not-contains', false));

        $flag = $this->client->withContext($c1)->single('parity-attr-not-contains', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithContextDelegatesAttributeNotContainsPositive(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-attr-not-contains', false, $this->makeBoolFlag('parity-attr-not-contains', true));

        $flag = $this->client->withContext($c2)->single('parity-attr-not-contains', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesAttributeNotInNegative(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-attr-not-in', false, $this->makeBoolFlag('parity-attr-not-in', false));

        $flag = $this->client->withContext($c1)->single('parity-attr-not-in', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithContextDelegatesAttributeNotInPositive(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-attr-not-in', false, $this->makeBoolFlag('parity-attr-not-in', true));

        $flag = $this->client->withContext($c2)->single('parity-attr-not-in', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesAttributeStartsWithNegative(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-attr-starts-with', false, $this->makeBoolFlag('parity-attr-starts-with', false));

        $flag = $this->client->withContext($c1)->single('parity-attr-starts-with', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithContextDelegatesAttributeGtNegative(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-attr-gt', false, $this->makeBoolFlag('parity-attr-gt', false));

        $flag = $this->client->withContext($c1)->single('parity-attr-gt', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithContextDelegatesAttributeGtPositive(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-attr-gt', false, $this->makeBoolFlag('parity-attr-gt', true));

        $flag = $this->client->withContext($c2)->single('parity-attr-gt', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesAttributeLtPositive(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-attr-lt', false, $this->makeBoolFlag('parity-attr-lt', true));

        $flag = $this->client->withContext($c1)->single('parity-attr-lt', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesAttributeLtNegative(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-attr-lt', false, $this->makeBoolFlag('parity-attr-lt', false));

        $flag = $this->client->withContext($c2)->single('parity-attr-lt', false);

        $this->assertFalse($flag->asBool());
    }

    public function testWithContextDelegatesAttributeLtePositive(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-attr-lte', false, $this->makeBoolFlag('parity-attr-lte', true));

        $flag = $this->client->withContext($c1)->single('parity-attr-lte', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesAttributeLteBoundary(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-attr-lte', false, $this->makeBoolFlag('parity-attr-lte', true));

        $flag = $this->client->withContext($c2)->single('parity-attr-lte', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesContextNotEqualsPositive(): void
    {
        $c1 = $this->c1();
        $this->expectContextThenSingle($c1, 'parity-ctx-not-equals', false, $this->makeBoolFlag('parity-ctx-not-equals', true));

        $flag = $this->client->withContext($c1)->single('parity-ctx-not-equals', false);

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextDelegatesContextNotEqualsNegative(): void
    {
        $c2 = $this->c2();
        $this->expectContextThenSingle($c2, 'parity-ctx-not-equals', false, $this->makeBoolFlag('parity-ctx-not-equals', false));

        $flag = $this->client->withContext($c2)->single('parity-ctx-not-equals', false);

        $this->assertFalse($flag->asBool());
    }
}
