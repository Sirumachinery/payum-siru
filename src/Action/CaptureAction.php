<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Siru\Exception\ApiException;
use Siru\PayumSiru\Action\Api\BaseApiAwareAction;
use Siru\PayumSiru\Api;

/**
 * @property Api $api
 */
class CaptureAction extends BaseApiAwareAction implements LoggerAwareInterface
{

    use LoggerAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     * @throws HttpRedirect
     */
    public function execute($request) : void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (isset($model['siru_uuid'])) {
            return;
        }

        try {
            $response = $this->api->createPayment($model->toUnsafeArrayWithoutLocal());
            $model['siru_uuid'] = $response['uuid'];
        } catch(ApiException $e) {
            $this->logger?->error('Failed to create payment', $e->getErrorStack());
            throw $e;
        }
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
