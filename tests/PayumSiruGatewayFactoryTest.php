<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Tests;

use Http\Message\MessageFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\HttpClientInterface;
use PHPUnit\Framework\TestCase;
use Siru\PayumSiru\Action\CaptureAction;
use Siru\PayumSiru\Action\ConvertPaymentAction;
use Siru\PayumSiru\Action\NotifyAction;
use Siru\PayumSiru\Action\StatusAction;
use Siru\PayumSiru\Action\SyncAction;
use Siru\PayumSiru\Api;
use Siru\PayumSiru\PayumSiruGatewayFactory;

/**
 * @covers \Siru\PayumSiru\PayumSiruGatewayFactory
 */
class PayumSiruGatewayFactoryTest extends TestCase
{

    /**
     * @test
     * @return array<string, mixed>
     */
    public function createsGatewayConfig() : array
    {
        $factory = new PayumSiruGatewayFactory();
        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertArrayHasKey('payum.factory_title', $config);
        return $config;
    }

    /**
     * @test
     */
    public function createsGatewayConfigWithDefaultOptionsInConstructor() : void
    {
        $factory = new PayumSiruGatewayFactory(['foo' => 'bar']);
        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('foo', $config);
        $this->assertEquals('bar', $config['foo']);
    }

    /**
     * @test
     */
    public function throwsExceptionWithoutRequiredOptions() : void
    {
        $factory = new PayumSiruGatewayFactory();
        $this->expectException(\LogicException::class);

        $factory->create();
    }

    /**
     * @param array<string, mixed> $config
     * @test
     * @depends createsGatewayConfig
     */
    public function gatewayConfigContainsDefaultOptions(array $config) : void
    {
        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertIsArray($config['payum.default_options']);
        $this->assertArrayHasKey('sandbox', $config['payum.default_options']);
        $this->assertArrayHasKey('disable_notify', $config['payum.default_options']);
    }

    /**
     * @param array<string, mixed> $config
     * @test
     * @depends createsGatewayConfig
     */
    public function gatewayConfigDefinesRequiredOptions(array $config) : void
    {
        $this->assertArrayHasKey('payum.required_options', $config);
        $this->assertIsArray($config['payum.required_options']);
        $this->assertContains('merchant_id', $config['payum.required_options']);
        $this->assertContains('merchant_secret', $config['payum.required_options']);
        $this->assertContains('variant', $config['payum.required_options']);
        $this->assertContains('purchase_country', $config['payum.required_options']);
        $this->assertContains('service_group', $config['payum.required_options']);
        $this->assertContains('tax_class', $config['payum.required_options']);
    }

    /**
     * @param array<string, mixed> $config
     * @test
     * @depends createsGatewayConfig
     */
    public function gatewayConfigDefinesActions(array $config) : void
    {
        $this->assertArrayHasKey('payum.action.capture', $config);
        $this->assertInstanceOf(CaptureAction::class, $config['payum.action.capture']);
        $this->assertArrayHasKey('payum.action.notify', $config);
        $this->assertInstanceOf(NotifyAction::class, $config['payum.action.notify']);
        $this->assertArrayHasKey('payum.action.status', $config);
        $this->assertInstanceOf(StatusAction::class, $config['payum.action.status']);
        $this->assertArrayHasKey('payum.action.convert_payment', $config);
        $this->assertInstanceOf(ConvertPaymentAction::class, $config['payum.action.convert_payment']);
        $this->assertArrayHasKey('payum.action.sync', $config);
        $this->assertInstanceOf(SyncAction::class, $config['payum.action.sync']);
    }

    /**
     * @test
     */
    public function createsApi() : void
    {
        $factory = new PayumSiruGatewayFactory([
            'variant' => 'variant2',
            'purchase_country' => 'FI',
            'service_group' => 2,
            'tax_class' => 2,
            'merchant_id' => 1,
            'merchant_secret' => '123',
        ]);
        $config = $factory->createConfig();
        $config['payum.http_client'] = $this->createMock(HttpClientInterface::class);
        $config['httplug.message_factory'] = $this->createMock(MessageFactory::class);
        $api = $config['payum.api'](new ArrayObject($config));
        $this->assertInstanceOf(Api::class, $api);
    }

}