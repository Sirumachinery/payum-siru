<?php
declare(strict_types=1);

namespace Siru\PayumSiru;

use Siru\PayumSiru\Action\CancelAction;
use Siru\PayumSiru\Action\ConvertPaymentAction;
use Siru\PayumSiru\Action\CaptureAction;
use Siru\PayumSiru\Action\SyncAction;
use Siru\PayumSiru\Action\NotifyAction;
use Siru\PayumSiru\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class PayumSiruGatewayFactory extends GatewayFactory
{

    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config) : void
    {
        $config->defaults([
            'payum.factory_name' => 'payum-siru',
            'payum.factory_title' => 'Siru Mobile',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.sync' => new SyncAction()
        ]);

        if (!$config['payum.api']) {
            $config['payum.default_options'] = array(
                'sandbox' => true,
                'disable_notify' => false,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [
                'merchant_id',
                'merchant_secret',
                'variant',
                'purchase_country',
                'service_group',
                'tax_class'
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }

}
