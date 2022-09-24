<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Tests\Action;

use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Siru\PayumSiru\Action\CaptureAction;
use Siru\PayumSiru\Api;

/**
 * @covers \Siru\PayumSiru\Action\CaptureAction
 */
class CaptureActionTest extends AbstractActionTest
{

    /**
     * @test
     */
    public function doesNothingIfSiruUuidExists() : void
    {
        $api = $this->createMock(Api::class);
        $api
            ->expects($this->never())
            ->method('createPayment');
        $action = $this->createAction();
        $action->setApi($api);

        $action->execute(new Capture(['siru_uuid' => '123']));
    }

    /**
     * @test
     */
    public function executes() : void
    {
        $api = $this->createMock(Api::class);
        $api
            ->expects($this->once())
            ->method('createPayment')
            ->with(['foo' => 'bar'])
            ->willReturn(['uuid' => 'abc123', 'redirect' => 'https://localhost/redirect']);

        $action = $this->createAction();
        $action->setApi($api);

        $model = new \ArrayObject(['foo' => 'bar']);
        $request = new Capture($model);

        $exception = null;
        try {
            $action->execute($request);
        } catch(HttpRedirect $exception) {
        }
        $this->assertNotNull($exception, 'Expected HttpRedirect exception.');
        $this->assertSame('https://localhost/redirect', $exception->getUrl());
        $this->assertArrayHasKey('siru_uuid', $model->getArrayCopy());
        $this->assertSame('abc123', $model['siru_uuid']);
    }

    /**
     * @return iterable<Capture[]>
     */
    public function supportsProvider() : iterable
    {
        yield [new Capture([])];
    }

    /**
     * @return iterable<Generic[]>
     */
    public function unsupportedProvider() : iterable
    {
        yield [new Capture('foo')];
        yield [new Cancel([])];
    }

    protected function createAction() : CaptureAction
    {
        return new CaptureAction();
    }

}
