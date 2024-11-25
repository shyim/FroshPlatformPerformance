<?php

namespace Frosh\Performance\Disabled;

use Shopware\Core\Checkout\Gateway\CheckoutGatewayInterface;
use Shopware\Core\Checkout\Gateway\CheckoutGatewayResponse;
use Shopware\Core\Checkout\Gateway\Command\Struct\CheckoutGatewayPayloadStruct;

class AppCheckoutGateway implements CheckoutGatewayInterface
{
    public function process(CheckoutGatewayPayloadStruct $payload): CheckoutGatewayResponse
    {
        return new CheckoutGatewayResponse(
            $payload->getPaymentMethods(),
            $payload->getShippingMethods(),
            $payload->getCart()->getErrors()
        );
    }
}
