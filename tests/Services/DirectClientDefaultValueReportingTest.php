<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Zenmanage\Api\ApiClientInterface;
use Zenmanage\Api\Response\RulesResponse;
use Zenmanage\Cache\NullCache;
use Zenmanage\Flags\DefaultsCollection;
use Zenmanage\Flags\Flag;
use Zenmanage\Flags\FlagManager;
use Zenmanage\Laravel\Services\DirectClient;
use Zenmanage\Rules\RuleEngine;

/**
 * Wires DirectClient to a REAL FlagManager (rather than a mocked
 * FlagManagerInterface, as the rest of this suite uses) to prove the
 * default-value usage-reporting fix from zenmanage-php ^5.1.0 (ZEN-962)
 * actually reaches ApiClient::reportUsage() through the Laravel wrapper.
 *
 * @internal
 *
 * @covers \Zenmanage\Laravel\Services\DirectClient
 */
class DirectClientDefaultValueReportingTest extends TestCase
{
    public function testSingleReportsInlineDefaultValueThroughToApiClient(): void
    {
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->method('getRules')->willReturn(new RulesResponse('v1', []));
        $apiClient->expects($this->once())
            ->method('reportUsage')
            ->with('missing-flag', null, 'fallback-value')
        ;

        $flag = $this->makeClient($apiClient)->single('missing-flag', 'fallback-value');

        $this->assertSame('fallback-value', $flag->asString());
    }

    public function testSingleReportsDefaultsCollectionValueThroughToApiClient(): void
    {
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->method('getRules')->willReturn(new RulesResponse('v1', []));
        $apiClient->expects($this->once())
            ->method('reportUsage')
            ->with('missing-flag', null, 42)
        ;

        $defaults = DefaultsCollection::fromArray(['missing-flag' => 42]);
        $flag = $this->makeClient($apiClient)->withDefaults($defaults)->single('missing-flag');

        $this->assertSame(42, $flag->asNumber());
    }

    public function testSingleDoesNotReportDefaultValueWhenFlagIsFound(): void
    {
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->method('getRules')->willReturn(new RulesResponse('v1', [
            Flag::fromArray([
                'version' => 'fla_1',
                'type' => 'boolean',
                'key' => 'found-flag',
                'name' => 'Found Flag',
                'target' => [
                    'version' => 'tar_1',
                    'expired_at' => null,
                    'published_at' => null,
                    'scheduled_at' => null,
                    'value' => ['version' => 'v1', 'value' => ['boolean' => true]],
                ],
                'rules' => [],
            ]),
        ]));
        $apiClient->expects($this->once())
            ->method('reportUsage')
            ->with('found-flag', $this->anything(), null)
        ;

        $flag = $this->makeClient($apiClient)->single('found-flag', false);

        $this->assertTrue($flag->asBool());
    }

    private function makeClient(ApiClientInterface $apiClient): DirectClient
    {
        $flagManager = new FlagManager(
            apiClient: $apiClient,
            cache: new NullCache(),
            ruleEngine: new RuleEngine(),
            cacheTtl: 60,
        );

        return new DirectClient($flagManager);
    }
}
