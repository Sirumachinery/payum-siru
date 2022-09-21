<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Action;

use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Siru\PayumSiru\Action\Api\BaseApiAwareAction;
use Siru\PayumSiru\Api;

/**
 * @property Api $api
 */
class StatusAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($model['siru_uuid'])) {
            $request->markNew();
            return;
        }

        $status = $this->api->checkStatus($model['siru_uuid']);
        switch ($status['status']) {
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
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
