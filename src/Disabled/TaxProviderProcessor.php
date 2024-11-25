<?php

namespace Frosh\Performance\Disabled;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class TaxProviderProcessor extends \Shopware\Core\Checkout\Cart\TaxProvider\TaxProviderProcessor
{
    public function __construct()
    {
    }

    public function process(Cart $cart, SalesChannelContext $context): void
    {
    }
}
