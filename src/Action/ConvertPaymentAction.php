<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use Siru\PayumSiru\PriceHelper;

class ConvertPaymentAction implements ActionInterface, GenericTokenFactoryAwareInterface
{

    use GatewayAwareTrait,
        GenericTokenFactoryAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request) : void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var TokenInterface $token */
        $token = $request->getToken();
        $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails());

        $details = [
            'currency' => $payment->getCurrencyCode(),
            'basePrice' => PriceHelper::formatPrice($payment->getTotalAmount()),
            'purchaseReference' => $payment->getNumber(),
            'redirectAfterSuccess' => $token->getTargetUrl(),
            'redirectAfterCancel' => $token->getTargetUrl(),
            'redirectAfterFailure' => $token->getTargetUrl(),
            'notifyAfterSuccess' => $notifyToken->getTargetUrl(),
            'notifyAfterCancel' => $notifyToken->getTargetUrl(),
            'notifyAfterFailure' => $notifyToken->getTargetUrl(),
            'title' => $payment->getDescription(),
            'customerEmail' => $payment->getClientEmail()
        ];

        $request->setResult($details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) : bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array'
        ;
    }
}
