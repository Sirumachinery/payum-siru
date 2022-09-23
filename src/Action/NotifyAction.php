<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Siru\PayumSiru\Action\Api\BaseApiAwareAction;
use Siru\PayumSiru\Api;

/**
 * @property Api $api
 */
class NotifyAction extends BaseApiAwareAction implements LoggerAwareInterface
{

    use LoggerAwareTrait;

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
            $this->logger?->warning('Siru NotifyAction called with empty HTTP request body');
            return;
        }

        if (!$this->api->isNotificationAuthentic($fields)) {
            // signature does not match
            $this->logger?->warning('Siru NotifyAction called with invalid signature in request', $fields);
            return;
        }

        $this->logger?->debug('Siru notification', [$fields['siru_event'], $fields['siru_uuid']]);
        $status = match ($fields['siru_event']) {
            'success' => 'confirmed',
            'cancel' => 'canceled',
            'failure' => 'failed',
            default => null
        };
        if (null !== $status) {
            $model['siru_status'] = $status;
        }
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
