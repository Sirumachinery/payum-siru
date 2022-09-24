<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Tests\Action;

use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Payum\Core\Request\Sync;
use Siru\PayumSiru\Action\SyncAction;
use Siru\PayumSiru\Api;

/**
 * @covers \Siru\PayumSiru\Action\SyncAction
 */
class SyncActionTest extends AbstractActionTest
{

    /**
     * @test
     */
    public function executes() : void
    {
        $api = $this->createMock(Api::class);
        $api
            ->expects($this->once())
            ->method('checkStatus')
            ->with('abc123')
            ->willReturn(['uuid' => 'abc123', 'status' => 'confirmed']);

        $action = $this->createAction();
        $action->setApi($api);

        $model = new \ArrayObject(['siru_uuid' => 'abc123']);
        $action->execute(new Sync($model));
        $this->assertArrayHasKey('siru_status', $model->getArrayCopy());
        $this->assertEquals('confirmed', $model['siru_status']);
    }

    /**
     * @return iterable<Sync[]>
     */
    public function supportsProvider() : iterable
    {
        yield [new Sync([])];
    }

    /**
     * @return iterable<Generic[]>
     */
    public function unsupportedProvider() : iterable
    {
        yield [new Sync('foo')];
        yield [new Capture([])];
    }

    protected function createAction() : SyncAction
    {
        return new SyncAction();
    }

}
