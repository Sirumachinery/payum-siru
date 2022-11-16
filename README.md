# Payum Siru Mobile payment gateway

This library allows the use of [Siru Mobile](https://sirumobile.com) payments with Payum.

## Requirements

- PHP 8.0+
- API credentials from Siru Mobile

## Installation

```shell
composer require sirumobile/payum-siru
```

## Configuration

You need your API credentials and integration details from Siru Mobile. See [EXAMPLE](EXAMPLE.md) for more complete
example of the payment flow.

```php
<?php

use Payum\Core\GatewayFactoryInterface;
use Siru\PayumSiru\PayumSiruGatewayFactory;
use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGatewayFactory('siru_checkout', function(array $config, GatewayFactoryInterface $coreGatewayFactory) {
        return new PayumSiruGatewayFactory($config, $coreGatewayFactory);
    })
    ->addGateway('siru_checkout', [
        'factory' => 'siru_checkout',
        # These are only example values. Replace these with values you received from Siru Mobile
        'merchant_id' => 123,
        'merchant_secret' => 'yoursecret',
        'variant' => 'variant2',
        'purchase_country' => 'FI',
        'service_group' => 2,
        'tax_class' => 3,
    ])
    ->getPayum()
;
```
