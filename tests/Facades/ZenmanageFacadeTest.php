<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Zenmanage\Laravel\Contracts\Client;
use Zenmanage\Laravel\Facades\Zenmanage;

/**
 * @internal
 */
#[CoversClass(Zenmanage::class)]
class ZenmanageFacadeTest extends TestCase
{
    public function testGetFacadeAccessorReturnsClientClass(): void
    {
        if (false === class_exists('\Illuminate\Support\Facades\Facade')) {
            $this->markTestSkipped('Laravel Facade class not available');
        }

        $reflection = new ReflectionClass(Zenmanage::class);
        $method = $reflection->getMethod('getFacadeAccessor');

        $result = $method->invoke(null);

        $this->assertSame(Client::class, $result);
    }
}
