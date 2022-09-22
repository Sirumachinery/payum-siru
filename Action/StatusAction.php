<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Sync;

class StatusAction implements ActionInterface, GatewayAwareInterface
{

    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request) : void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($model['siru_uuid'])) {
            $request->markNew();
            return;
        }

        if (!isset($model['siru_status'])) {
            $this->gateway->execute(new Sync($model));
        }

        switch ($model['siru_status']) {
            case 'confirmed':
                $request->markCaptured();
                break;
            case 'canceled':
                $request->markCanceled();
                break;
            case 'failed':
                $request->markFailed();
                break;
            default:
                $request->markPending();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) : bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }

}
