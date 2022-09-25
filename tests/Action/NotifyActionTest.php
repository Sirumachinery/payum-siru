<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Tests\Action;

use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Siru\PayumSiru\Action\NotifyAction;
use Siru\PayumSiru\Api;

/**
 * @covers \Siru\PayumSiru\Action\NotifyAction
 */
class NotifyActionTest extends AbstractActionTest
{

    /**
     * @test
     */
    public function doesNothingWithInvalidJson() : void
    {
        $json = 'null';

        $gateway = $this->createMock(GatewayInterface::class);
        $gateway
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function(GetHttpRequest $request) use ($json) {
                $request->content = $json;
                return $request;
            });

        $api = $this->createMock(Api::class);
        $api
            ->expects($this->never())
            ->method('isNotificationAuthentic');

        $action = $this->createAction();
        $action->setGateway($gateway);
        $action->setApi($api);

        $model = new \ArrayObject();
        $action->execute(new Notify($model));
        $this->assertArrayNotHasKey('siru_status', $model->getArrayCopy());
    }

    /**
     * @test
     */
    public function doesNothingWithInvalidSignatureInJson() : void
    {
        $json = '{"foo":"bar"}';

        $gateway = $this->createMock(GatewayInterface::class);
        $gateway
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function(GetHttpRequest $request) use ($json) {
                $request->content = $json;
                return $request;
            });

        $api = $this->createMock(Api::class);
        $api
            ->expects($this->once())
            ->method('isNotificationAuthentic')
            ->with($this->callback(fn(array $payload) => $payload === ['foo' => 'bar']))
            ->willReturn(false);

        $action = $this->createAction();
        $action->setGateway($gateway);
        $action->setApi($api);

        $model = new \ArrayObject();
        $action->execute(new Notify($model));
        $this->assertArrayNotHasKey('siru_status', $model->getArrayCopy());
    }

    /**
     * @test
     * @dataProvider statusProvider
     */
    public function savesStatusToModel(string $event, ?string $expected) : void
    {
        $json = '{"siru_event":"' . $event . '"}';

        $gateway = $this->createMock(GatewayInterface::class);
        $gateway
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function(GetHttpRequest $request) use ($json) {
                $request->content = $json;
                return $request;
            });

        $api = $this->createMock(Api::class);
        $api
            ->expects($this->once())
            ->method('isNotificationAuthentic')
            ->willReturn(true);

        $action = $this->createAction();
        $action->setGateway($gateway);
        $action->setApi($api);

        $model = new \ArrayObject();
        $action->execute(new Notify($model));
        if (null === $expected) {
            $this->assertArrayNotHasKey('siru_status', $model->getArrayCopy());
        } else {
            $this->assertTrue($model->offsetExists('siru_status'));
            $this->assertEquals($expected, $model['siru_status']);
        }
    }

    /**
     * @return iterable<array{string, string|null}>
     */
    public function statusProvider() : iterable
    {
        yield ['success', 'confirmed'];
        yield ['cancel', 'canceled'];
        yield ['failure', 'failed'];
        yield ['foo', null];
    }

    /**
     * @return iterable<Notify[]>
     */
    public function supportsProvider() : iterable
    {
        yield [new Notify([])];
    }

    /**
     * @return iterable<Generic[]>
     */
    public function unsupportedProvider() : iterable
    {
        yield [new Notify('foo')];
        yield [new Capture([])];
    }

    protected function createAction() : NotifyAction
    {
        return new NotifyAction();
    }

}
