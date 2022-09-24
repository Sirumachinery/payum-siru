<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Tests\Action;

use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Sync;
use Siru\PayumSiru\Action\StatusAction;

/**
 * @covers \Siru\PayumSiru\Action\StatusAction
 */
class StatusActionTest extends AbstractActionTest
{

    /**
     * @test
     * @dataProvider requestProvider
     */
    public function updatesRequest(GetStatusInterface $request, string $expectedStatus) : void
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $action = $this->createAction();
        $action->setGateway($gateway);

        $action->execute($request);
        $this->assertSame($expectedStatus, $request->getValue());
    }

    /**
     * @test
     */
    public function synchronizesWithGatewayIfNeeded() : void
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $gateway
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class));

        $action = $this->createAction();
        $action->setGateway($gateway);

        $model = new \ArrayObject(['siru_uuid' => 'abc123']);
        $action->execute(new GetHumanStatus($model));
    }

    /**
     * @return iterable<GetStatusInterface[]>
     */
    public function supportsProvider() : iterable
    {
        yield [new GetHumanStatus([])];
        yield [new GetBinaryStatus([])];
    }

    /**
     * @return iterable<Generic[]>
     */
    public function unsupportedProvider() : iterable
    {
        yield [new GetHumanStatus('foo')];
        yield [new Capture([])];
    }

    /**
     * @return iterable<array{0: GetHumanStatus, 1: string}>
     */
    public function requestProvider() : iterable
    {
        yield [new GetHumanStatus([]), GetHumanStatus::STATUS_NEW];
        yield [new GetHumanStatus(['siru_uuid' => 'a']), GetHumanStatus::STATUS_PENDING];
        yield [new GetHumanStatus(['siru_uuid' => 'a', 'siru_status' => 'confirmed']), GetHumanStatus::STATUS_CAPTURED];
        yield [new GetHumanStatus(['siru_uuid' => 'a', 'siru_status' => 'canceled']), GetHumanStatus::STATUS_CANCELED];
        yield [new GetHumanStatus(['siru_uuid' => 'a', 'siru_status' => 'failed']), GetHumanStatus::STATUS_FAILED];
    }

    protected function createAction() : StatusAction
    {
        return new StatusAction();
    }

}
