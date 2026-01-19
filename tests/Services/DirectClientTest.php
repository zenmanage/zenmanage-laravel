<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
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
}
