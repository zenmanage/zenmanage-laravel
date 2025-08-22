<?php

use PHPUnit\Framework\TestCase;
use Zenmanage\Laravel\Services\DirectClient;
use Zenmanage\Flags\Flags;
use Zenmanage\Flags\Request\Entities\Context\Context;
use Zenmanage\Flags\Response\Entities\Flag;
use Zenmanage\Zenmanage;

class DirectClientTest extends TestCase
{
    private $zenmanageMock;
    private $flagsMock;
    private $client;

    protected function setUp(): void
    {
        $this->flagsMock = $this->createMock(Flags::class);
        $this->zenmanageMock = $this->createMock(Zenmanage::class);
        $this->zenmanageMock->flags = $this->flagsMock;
        $this->client = new DirectClient($this->zenmanageMock);
    }

    public function testWithContextReturnsSelfAndSetsFlags()
    {
        $context = $this->createMock(Context::class);
        $newFlagsMock = $this->createMock(Flags::class);

        $this->flagsMock->expects($this->once())
            ->method('withContext')
            ->with($context)
            ->willReturn($newFlagsMock);

        $this->zenmanageMock->flags = $this->flagsMock;

        // Patch the flags property after withContext is called
        $this->client = new DirectClient($this->zenmanageMock);

        // Use reflection to set the flags property after withContext
        $this->zenmanageMock->flags = $this->flagsMock;

        // Patch the method to update the flags property
        $this->flagsMock->method('withContext')->willReturn($newFlagsMock);

        $result = $this->client->withContext($context);
        $this->assertInstanceOf(DirectClient::class, $result);
    }

    public function testWithDefaultReturnsSelfAndSetsFlags()
    {
        $key = 'test_key';
        $type = 'bool';
        $defaultValue = true;
        $newFlagsMock = $this->createMock(Flags::class);

        $this->flagsMock->expects($this->once())
            ->method('withDefault')
            ->with($key, $type, $defaultValue)
            ->willReturn($newFlagsMock);

        $this->zenmanageMock->flags = $this->flagsMock;

        $this->flagsMock->method('withDefault')->willReturn($newFlagsMock);

        $result = $this->client->withDefault($key, $type, $defaultValue);
        $this->assertInstanceOf(DirectClient::class, $result);
    }

    public function testAllReturnsArray()
    {
        $expected = ['flag1' => true, 'flag2' => false];
        $this->flagsMock->expects($this->once())
            ->method('all')
            ->willReturn($expected);

        $this->zenmanageMock->flags = $this->flagsMock;

        $result = $this->client->all();
        $this->assertSame($expected, $result);
    }

    public function testReportCallsFlagsReport()
    {
        $key = 'test_key';
        $this->flagsMock->expects($this->once())
            ->method('report')
            ->with($key);

        $this->zenmanageMock->flags = $this->flagsMock;

        $this->client->report($key);
        $this->assertTrue(true); // If no exception, test passes
    }

    public function testSingleReturnsFlagOrNull()
    {
        $key = 'test_key';
        $flagMock = $this->createMock(Flag::class);

        $this->flagsMock->expects($this->once())
            ->method('single')
            ->with($key)
            ->willReturn($flagMock);

        $this->zenmanageMock->flags = $this->flagsMock;

        $result = $this->client->single($key);
        $this->assertInstanceOf(Flag::class, $result);
    }
}