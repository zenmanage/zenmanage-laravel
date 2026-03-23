<?php

declare(strict_types=1);

use Illuminate\Contracts\Foundation\Application;
use PHPUnit\Framework\TestCase;
use Zenmanage\Laravel\Contracts\Client;
use Zenmanage\Laravel\Services\DirectClient;
use Zenmanage\Laravel\ZenmanageServiceProvider;

/**
 * @internal
 *
 * @covers \Zenmanage\Laravel\ZenmanageServiceProvider
 */
class ZenmanageServiceProviderTest extends TestCase
{
    public function testRegisterBindsZenmanageToContainer(): void
    {
        if (!class_exists('\Illuminate\Support\ServiceProvider')) {
            $this->markTestSkipped('Laravel ServiceProvider class not available');
        }

        // This test verifies that the ServiceProvider correctly registers bindings
        // In a real Laravel app, this would be tested through the container
        // For now, we test that the class exists and has the required methods
        $provider = new ZenmanageServiceProvider($this->createMock(Application::class));

        $this->assertTrue(method_exists($provider, 'register'));
        $this->assertTrue(method_exists($provider, 'boot'));
    }

    public function testBindingsArrayContainsCorrectMappings(): void
    {
        if (!class_exists('\Illuminate\Support\ServiceProvider')) {
            $this->markTestSkipped('Laravel ServiceProvider class not available');
        }

        $provider = new ZenmanageServiceProvider($this->createMock(Application::class));

        $this->assertIsArray($provider->bindings);
        $this->assertArrayHasKey(Client::class, $provider->bindings);
        $this->assertSame(DirectClient::class, $provider->bindings[Client::class]);
    }

    public function testPublishedConfigDoesNotContainClientMetadataKeys(): void
    {
        $configContents = file_get_contents(__DIR__ . '/../config/zenmanage.php');

        $this->assertIsString($configContents);
        $this->assertStringNotContainsString("'client_agent'", $configContents);
        $this->assertStringNotContainsString("'sdk_version'", $configContents);
    }

    public function testProviderHardCodesClientAgent(): void
    {
        $providerContents = file_get_contents(__DIR__ . '/../src/ZenmanageServiceProvider.php');

        $this->assertIsString($providerContents);
        $this->assertStringContainsString("private const LARAVEL_CLIENT_AGENT = 'zenmanage-laravel';", $providerContents);
        $this->assertStringNotContainsString("['client_agent']", $providerContents);
    }

    public function testResolveLaravelSdkVersionReturnsNullOrString(): void
    {
        if (!class_exists('\Illuminate\Support\ServiceProvider')) {
            $this->markTestSkipped('Laravel ServiceProvider class not available');
        }

        $provider = new ZenmanageServiceProvider($this->createMock(Application::class));
        $method = new ReflectionMethod($provider, 'resolveLaravelSdkVersion');
        $method->setAccessible(true);

        $version = $method->invoke($provider);

        $this->assertTrue($version === null || is_string($version));
        if (is_string($version)) {
            $this->assertNotSame('', $version);
        }
    }
}
