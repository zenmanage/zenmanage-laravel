<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Zenmanage\Laravel\Contracts\Client;
use Zenmanage\Laravel\Http\Controllers\WebhookController;

/**
 * @internal
 *
 * @covers \Zenmanage\Laravel\Http\Controllers\WebhookController
 */
class WebhookControllerTest extends TestCase
{
    private const SECRET = 'whsec_test_secret';
    private const PAYLOAD = '{"action":"target_rule.updated"}';

    public function testReturns500WhenSecretIsNotConfigured(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('refreshRules');

        $controller = new WebhookController($client, '');
        $request = Request::create('/zenmanage/webhook', 'POST', content: self::PAYLOAD);

        $response = $controller($request);

        $this->assertSame(500, $response->getStatusCode());
    }

    public function testReturns401WhenSignatureHeaderIsMissing(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('refreshRules');

        $controller = new WebhookController($client, self::SECRET);
        $request = Request::create('/zenmanage/webhook', 'POST', content: self::PAYLOAD);

        $response = $controller($request);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testReturns401WhenSignatureIsInvalid(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('refreshRules');

        $controller = new WebhookController($client, self::SECRET);
        $request = Request::create('/zenmanage/webhook', 'POST', content: self::PAYLOAD, server: [
            'HTTP_X_ZENMANAGE_SIGNATURE' => 'sha256='.str_repeat('0', 64),
        ]);

        $response = $controller($request);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testRefreshesRulesAndReturns200WhenSignatureIsValid(): void
    {
        $signature = 'sha256='.hash_hmac('sha256', self::PAYLOAD, self::SECRET);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('refreshRules');

        $controller = new WebhookController($client, self::SECRET);
        $request = Request::create('/zenmanage/webhook', 'POST', content: self::PAYLOAD, server: [
            'HTTP_X_ZENMANAGE_SIGNATURE' => $signature,
        ]);

        $response = $controller($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['message' => 'Flag rules refreshed.'], $response->getData(true));
    }

    public function testDoesNotRefreshRulesWhenSignatureIsInvalid(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('refreshRules');

        $controller = new WebhookController($client, self::SECRET);
        $request = Request::create('/zenmanage/webhook', 'POST', content: 'tampered', server: [
            'HTTP_X_ZENMANAGE_SIGNATURE' => 'sha256='.hash_hmac('sha256', self::PAYLOAD, self::SECRET),
        ]);

        $controller($request);
    }
}
