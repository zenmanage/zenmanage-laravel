<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Zenmanage\Laravel\Contracts\Client;
use Zenmanage\Laravel\Facades\Zenmanage;

/**
 * @internal
 *
 * @covers \Zenmanage\Laravel\Facades\Zenmanage
 */
class ZenmanageFacadeTest extends TestCase
{
    public function testGetFacadeAccessorReturnsClientClass(): void
    {
        if (!class_exists('\Illuminate\Support\Facades\Facade')) {
            $this->markTestSkipped('Laravel Facade class not available');
        }

        $reflection = new ReflectionClass(Zenmanage::class);
        $method = $reflection->getMethod('getFacadeAccessor');
        $method->setAccessible(true);

        $result = $method->invoke(null);

        $this->assertSame(Client::class, $result);
    }
}
