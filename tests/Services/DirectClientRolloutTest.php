<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Zenmanage\Flags\Context\Context;
use Zenmanage\Flags\Flag;
use Zenmanage\Flags\FlagManagerInterface;
use Zenmanage\Flags\Rollout;
use Zenmanage\Flags\RolloutBucket;
use Zenmanage\Flags\Target;
use Zenmanage\Laravel\Services\DirectClient;
use Zenmanage\Rules\RuleValue;

/**
 * Tests that percentage rollout features from zenmanage-php >=3.1.0
 * work correctly through the Laravel DirectClient facade layer.
 *
 * @internal
 *
 * @covers \Zenmanage\Laravel\Services\DirectClient
 */
class DirectClientRolloutTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Rollout classes are available (dependency check)
    // -------------------------------------------------------------------------

    public function testRolloutClassExists(): void
    {
        $this->assertTrue(class_exists(Rollout::class));
    }

    public function testRolloutBucketClassExists(): void
    {
        $this->assertTrue(class_exists(RolloutBucket::class));
    }

    // -------------------------------------------------------------------------
    // RolloutBucket cross-SDK vectors
    // -------------------------------------------------------------------------

    public function testRolloutBucketMatchesCrossSdkVector(): void
    {
        // test-salt + user-0 => bucket 34 => in bucket at 50%
        $this->assertTrue(RolloutBucket::isInBucket('test-salt', 'user-0', 50));
        // test-salt + user-2 => bucket 98 => NOT in bucket at 50%
        $this->assertFalse(RolloutBucket::isInBucket('test-salt', 'user-2', 50));
    }

    public function testRolloutBucketNullIdentifierReturnsFalse(): void
    {
        $this->assertFalse(RolloutBucket::isInBucket('any-salt', null, 100));
    }

    // -------------------------------------------------------------------------
    // Flag with rollout data
    // -------------------------------------------------------------------------

    public function testFlagFromArrayParsesRollout(): void
    {
        $flagData = [
            'version' => 'fla_1',
            'type' => 'boolean',
            'key' => 'rollout-flag',
            'name' => 'Rollout Flag',
            'target' => [
                'version' => 'tar_1',
                'expired_at' => null,
                'published_at' => '2026-02-20T00:00:00+00:00',
                'scheduled_at' => null,
                'value' => ['version' => 'v1', 'value' => ['boolean' => false]],
            ],
            'rules' => [],
            'rollout' => [
                'target' => [
                    'version' => 'tar_ro',
                    'expired_at' => null,
                    'published_at' => '2026-02-24T00:00:00+00:00',
                    'scheduled_at' => null,
                    'value' => ['version' => 'v1', 'value' => ['boolean' => true]],
                ],
                'rules' => [],
                'percentage' => 50,
                'salt' => 'test-salt',
                'status' => 'active',
            ],
        ];

        $flag = Flag::fromArray($flagData);

        $this->assertNotNull($flag->getRollout());
        $this->assertSame(50, $flag->getRollout()->getPercentage());
        $this->assertSame('test-salt', $flag->getRollout()->getSalt());
        $this->assertSame('active', $flag->getRollout()->getStatus());
    }

    public function testFlagFromArrayWithoutRollout(): void
    {
        $flagData = [
            'version' => 'fla_1',
            'type' => 'boolean',
            'key' => 'no-rollout',
            'name' => 'No Rollout',
            'target' => [
                'version' => 'tar_1',
                'expired_at' => null,
                'published_at' => null,
                'scheduled_at' => null,
                'value' => ['version' => 'v1', 'value' => ['boolean' => true]],
            ],
            'rules' => [],
        ];

        $flag = Flag::fromArray($flagData);
        $this->assertNull($flag->getRollout());
    }

    public function testRolloutJsonSerializeRoundTrip(): void
    {
        $flagData = [
            'version' => 'fla_1',
            'type' => 'boolean',
            'key' => 'rt',
            'name' => 'RT',
            'target' => [
                'version' => 'tar_1',
                'expired_at' => null,
                'published_at' => null,
                'scheduled_at' => null,
                'value' => ['version' => 'v1', 'value' => ['boolean' => false]],
            ],
            'rules' => [],
            'rollout' => [
                'target' => [
                    'version' => 'tar_ro',
                    'expired_at' => null,
                    'published_at' => null,
                    'scheduled_at' => null,
                    'value' => ['version' => 'v1', 'value' => ['boolean' => true]],
                ],
                'rules' => [],
                'percentage' => 25,
                'salt' => 'abc123',
                'status' => 'active',
            ],
        ];

        $flag = Flag::fromArray($flagData);
        $json = $flag->jsonSerialize();

        $this->assertArrayHasKey('rollout', $json);

        /** @var array<string, mixed> $rolloutJson */
        $rolloutJson = $json['rollout'];
        $this->assertSame(25, $rolloutJson['percentage']);
        $this->assertSame('abc123', $rolloutJson['salt']);
    }

    // -------------------------------------------------------------------------
    // DirectClient delegates rollout evaluation to FlagManager
    // -------------------------------------------------------------------------

    public function testSingleDelegatesRolloutEvaluation(): void
    {
        // The FlagManager internally handles rollout bucketing when evaluating.
        // Here we verify the DirectClient transparently passes through the result.
        $rolloutValue = new RuleValue('v1', ['boolean' => true]);
        $rolloutTarget = new Target('tar_ro', null, '2026-02-24T00:00:00+00:00', null, $rolloutValue);
        $rolloutFlag = new Flag('fla_1', 'boolean', 'rollout-flag', 'Rollout Flag', $rolloutTarget, []);

        $flagManager = $this->createMock(FlagManagerInterface::class);
        $flagManager->expects($this->once())
            ->method('single')
            ->with('rollout-flag')
            ->willReturn($rolloutFlag)
        ;

        $client = new DirectClient($flagManager);
        $flag = $client->single('rollout-flag');

        $this->assertTrue($flag->asBool());
    }

    public function testWithContextThenSingleDelegatesRolloutEvaluation(): void
    {
        $context = Context::single('user', 'user-0');

        $rolloutValue = new RuleValue('v1', ['boolean' => true]);
        $rolloutTarget = new Target('tar_ro', null, null, null, $rolloutValue);
        $rolloutFlag = new Flag('fla_1', 'boolean', 'rollout-flag', 'Rollout Flag', $rolloutTarget, []);

        $contextManager = $this->createMock(FlagManagerInterface::class);
        $contextManager->expects($this->once())
            ->method('single')
            ->with('rollout-flag')
            ->willReturn($rolloutFlag)
        ;

        $flagManager = $this->createMock(FlagManagerInterface::class);
        $flagManager->expects($this->once())
            ->method('withContext')
            ->with($context)
            ->willReturn($contextManager)
        ;

        $client = new DirectClient($flagManager);
        $flag = $client->withContext($context)->single('rollout-flag');

        $this->assertTrue($flag->asBool());
    }

    public function testAllDelegatesRolloutEvaluation(): void
    {
        $rolloutValue = new RuleValue('v1', ['boolean' => true]);
        $rolloutTarget = new Target('tar_ro', null, null, null, $rolloutValue);
        $rolloutFlag = new Flag('fla_1', 'boolean', 'rollout-flag', 'Rollout', $rolloutTarget, []);

        $normalValue = new RuleValue('v1', ['boolean' => false]);
        $normalTarget = new Target('tar_fb', null, null, null, $normalValue);
        $normalFlag = new Flag('fla_2', 'boolean', 'normal-flag', 'Normal', $normalTarget, []);

        $flagManager = $this->createMock(FlagManagerInterface::class);
        $flagManager->expects($this->once())
            ->method('all')
            ->willReturn([$rolloutFlag, $normalFlag])
        ;

        $client = new DirectClient($flagManager);
        $flags = $client->all();

        $this->assertCount(2, $flags);
        $this->assertTrue($flags[0]->asBool());
        $this->assertFalse($flags[1]->asBool());
    }
}
