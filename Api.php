<?php
declare(strict_types=1);

namespace Siru\PayumSiru;

use Http\Message\MessageFactory;
use Payum\Core\HttpClientInterface;
use Siru\PayumSiru\Bridge\SiruHttpTransport;
use Siru\Signature;

class Api
{

    protected HttpClientInterface $client;

    protected MessageFactory $messageFactory;

    protected array $options = [];

    /**
     * @param array<string, string|int|null|bool> $options
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

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

        return $paymentApi->createPayment();
    }

    public function checkStatus(string $uuid) : array
    {
        $api = $this->getApi();
        return $api->getPurchaseStatusApi()->findPurchaseByUuid($uuid);
    }

    private function getApi() : \Siru\API
    {
        $siruTransport = new SiruHttpTransport($this->client, $this->messageFactory);
        $siruTransport->setBaseUrl($this->getApiEndpoint());

        $signature = new Signature($this->options['merchantId'], $this->options['secret']);
        $api = new \Siru\API($signature, $siruTransport);
        $this->prepareDefaults($api);
        return $api;
    }

    private function prepareDefaults(\Siru\API $api) : void
    {
        $api->setDefaults([
            'variant' => $this->options['variant'],
            'taxClass' => $this->options['taxClass'],
            'serviceGroup' => $this->options['serviceGroup'],
            'purchaseCountry' => $this->options['purchaseCountry'],
        ]);
    }

    protected function getApiEndpoint() : string
    {
        return $this->options['sandbox'] ? 'https://staging.sirumobile.com' : 'https://payment.sirumobile.com';
    }
}
