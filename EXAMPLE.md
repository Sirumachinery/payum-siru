# Siru checkout example

This is a most basic example based on [Payum](https://github.com/Payum/Payum/blob/master/docs/index.md) example code.
You need your API credentials and integration details from Siru Mobile for this.

```php
<?php
// config.php

declare(strict_types=1);

$loader = require_once( __DIR__.'/vendor/autoload.php');

use Payum\Core\Bridge\PlainPhp\Security\TokenFactory;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\PayumBuilder;
use Payum\Core\Payum;
use Siru\PayumSiru\PayumSiruGatewayFactory;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->setTokenFactory(function($tokenStorage, $storageRegistry) {
        return new TokenFactory($tokenStorage, $storageRegistry, 'http://localhost:8000/');
    })
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

```php
<?php
// prepare.php

declare(strict_types=1);

include __DIR__.'/config.php';

use Payum\Core\Model\Payment;

$gatewayName = 'siru_checkout';

$storage = $payum->getStorage(Payment::class);

/** @var Payment $payment */
$payment = $storage->create();
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(1240);
$payment->setDescription('Payment description');
$payment->setClientId('client unique identifier');
$payment->setClientEmail('foo@example.com');

$storage->update($payment);

$tokenFactory = $payum->getTokenFactory();
$captureToken = $tokenFactory->createCaptureToken($gatewayName, $payment, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

```php
<?php
// capture.php

declare(strict_types=1);

use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;

include __DIR__.'/config.php';

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

/** @var \Payum\Core\GatewayInterface $gateway */
if ($reply = $gateway->execute(new Capture($token), true)) {
    if ($reply instanceof HttpRedirect) {
        header("Location: ".$reply->getUrl());
        die();
    }

    throw new \LogicException('Unsupported reply', null, $reply);
}

$payum->getHttpRequestVerifier()->invalidate($token);

header("Location: ".$token->getAfterUrl());
```

```php
<?php
// done.php

declare(strict_types=1);

use Payum\Core\Request\GetHumanStatus;

include __DIR__.'/config.php';

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gatewayName = $token->getGatewayName();
$gateway = $payum->getGateway($gatewayName);

// you can invalidate the token. The url could not be requested anymore.
// $payum->getHttpRequestVerifier()->invalidate($token);

// Once you have token you can get the model from the storage directly.
//$identity = $token->getDetails();
//$payment = $payum->getStorage($identity->getClass())->find($identity);

// or Payum can fetch the model for you while executing a request (Preferred).
$gateway->execute($status = new GetHumanStatus($token));
$payment = $status->getFirstModel();

echo '<pre>';
print_r([
    'status' => $status->getValue(),
    'payment' => [
        'total_amount' => $payment->getTotalAmount(),
        'currency_code' => $payment->getCurrencyCode(),
        'details' => $payment->getDetails(),
    ],
]);
echo '</pre>';

```

```php
<?php
// notify.php

use Payum\Core\Request\Notify;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;

include __DIR__.'/config.php';

/** @var Payum $payum */

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

try {
    $gateway->execute(new Notify($token));

    http_response_code(200);
    echo 'OK';
} catch (HttpResponse $reply) {
    foreach ($reply->getHeaders() as $name => $value) {
        header("$name: $value");
    }

    http_response_code($reply->getStatusCode());
    echo ($reply->getContent());

    exit;
} catch (ReplyInterface $reply) {
    throw new \LogicException('Unsupported reply', null, $reply);
}
```
