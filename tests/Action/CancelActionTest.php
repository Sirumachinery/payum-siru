<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Capture;
use Siru\PayumSiru\Action\CancelAction;

/**
 * @covers \Siru\PayumSiru\Action\CancelAction
 */
class CancelActionTest extends AbstractActionTest
{

    /**
     * @test
     */
    public function executes() : void
    {
        $this->expectException(\LogicException::class);
        $this->createAction()->execute(new Cancel([]));
    }

    /**
     * @return iterable<Cancel[]>
     */
    public function supportsProvider() : iterable
    {
        yield [new Cancel([])];
    }

    /**
     * @return iterable<ActionInterface[]>
     */
    public function unsupportedProvider() : iterable
    {
        yield [new Cancel('foo')];
        yield [new Capture([])];
    }

    protected function createAction() : CancelAction
    {
        return new CancelAction();
    }

}
