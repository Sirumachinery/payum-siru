<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use PHPUnit\Framework\TestCase;

abstract class AbstractActionTest extends TestCase
{

    /**
     * @test
     * @dataProvider supportsProvider
     */
    public function supports($request) : void
    {
        $action = $this->createAction();
        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     * @dataProvider unsupportedProvider
     */
    public function doesNotSupport($request) : void
    {
        $action = $this->createAction();
        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     * @dataProvider unsupportedProvider
     */
    public function throwsExceptionForUnsupportedRequests($request) : void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = $this->createAction();
        $action->execute($request);
    }

    abstract protected function createAction() : ActionInterface;

    abstract public function supportsProvider() : iterable;

    abstract public function unsupportedProvider() : iterable;

}
