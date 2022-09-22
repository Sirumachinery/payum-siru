<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Sync;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Siru\PayumSiru\Action\Api\BaseApiAwareAction;
use Siru\PayumSiru\Api;

/**
 * @property Api $api
 */
class SyncAction extends BaseApiAwareAction implements LoggerAwareInterface
{

    use LoggerAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Sync $request
     */
    public function execute($request) : void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->logger?->debug('Sync payment status from Siru API', ['uuid' => $model['siru_uuid']]);
        $status = $this->api->checkStatus($model['siru_uuid']);
        $model['siru_status'] = $status['status'];
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) : bool
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }

}
