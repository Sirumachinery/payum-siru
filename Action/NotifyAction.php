<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Siru\PayumSiru\Action\Api\BaseApiAwareAction;
use Siru\PayumSiru\Api;

/**
 * @property Api $api
 */
class NotifyAction extends BaseApiAwareAction
{

    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request) : void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        $fields = json_decode($httpRequest->content);
        if (!$fields) {
            // Invalid JSON, maybe this was not a notification from Siru?
            return;
        }

        if (!$this->api->isNotificationAuthentic($fields)) {
            // signature does not match, maybe worth logging?
            return;
        }

        $model['siru_status'] = match ($fields['siru_event']) {
            'success' => 'confirmed',
            'cancel' => 'canceled',
            'failure' => 'failed'
        };
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) : bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
