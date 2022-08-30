<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Siru\PayumSiru\Action\Api\BaseApiAwareAction;
use Siru\PayumSiru\Api;

/**
 * @property Api $api
 */
class CaptureAction extends BaseApiAwareAction
{

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request) : void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (isset($model['siru_uuid'])) {
            return;
        }

        $response = $this->api->createPayment($model->toUnsafeArrayWithoutLocal());
        $model['siru_uuid'] = $response['uuid'];
        throw new HttpRedirect($response['redirect']);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) : bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
