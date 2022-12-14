<?php
declare(strict_types=1);

namespace Siru\PayumSiru;

use Http\Message\MessageFactory;
use Payum\Core\HttpClientInterface;
use Siru\PayumSiru\Action\ConvertPaymentAction;
use Siru\PayumSiru\Bridge\SiruHttpTransport;
use Siru\Signature;

class Api
{

    /**
     * @param array<string, string|int|null|bool> $options
     */
    public function __construct(protected array $options, protected HttpClientInterface $client, protected MessageFactory $messageFactory)
    {}

    /**
     * @param array<string, int|string|bool|null> $fields
     * @return array{uuid: string, redirect: string}
     */
    public function createPayment(array $fields) : array
    {
        $api = $this->getApi();
        $notifyDisabled = $this->options['disable_notify'] ?? false;

        $paymentApi = $api->getPaymentApi();
        foreach ($fields as $key => $value) {
            if ($notifyDisabled && str_starts_with($key, 'notifyAfter')) {
                continue;
            }
            $paymentApi->set($key, $value);
        }

        // Variant2 payments require price without VAT
        if ('variant2' === $this->options['variant']) {
            $paymentApi->set('basePrice', PriceHelper::calculatePriceWithoutVat($fields['basePrice'], (int) $this->options['tax_class']));
        }

        return $paymentApi->createPayment();
    }

    /**
     * @return array<string, string|null|bool|int>
     */
    public function checkStatus(string $uuid) : array
    {
        $api = $this->getApi();
        return $api->getPurchaseStatusApi()->findPurchaseByUuid($uuid);
    }

    /**
     * @param array<string, string|null> $fields
     */
    public function isNotificationAuthentic(array $fields) : bool
    {
        $api = $this->getApi();
        return $api->getSignature()->isNotificationAuthentic($fields);
    }

    private function getApi() : \Siru\API
    {
        $siruTransport = new SiruHttpTransport($this->client, $this->messageFactory);
        $siruTransport->setBaseUrl($this->getApiEndpoint());

        $signature = new Signature($this->options['merchant_id'], $this->options['merchant_secret']);
        $api = new \Siru\API($signature, $siruTransport);
        $this->prepareDefaults($api);
        return $api;
    }

    private function prepareDefaults(\Siru\API $api) : void
    {
        $api->setDefaults([
            'variant' => $this->options['variant'],
            'taxClass' => (int) $this->options['tax_class'],
            'serviceGroup' => (int) $this->options['service_group'],
            'purchaseCountry' => $this->options['purchase_country'],
        ]);
    }

    protected function getApiEndpoint() : string
    {
        return $this->options['sandbox'] ? 'https://staging.sirumobile.com' : 'https://payment.sirumobile.com';
    }

}
