<?php

namespace IdeaInYou\BigCommerce\Plugin;

use Mirakl\MMP\Shop\Domain\Order\ShopOrder;

class UpdateMiraklEmailPlugin
{
    /**
     * @param \MiraklSeller\Sales\Model\Create\Quote $subject
     * @param callable $proceed
     * @param ShopOrder $miraklOrder
     * @return string
     */
    public function aroundGetCustomerEmail(\MiraklSeller\Sales\Model\Create\Quote $subject, callable $proceed, ShopOrder $miraklOrder)
    {
        return $miraklOrder->getCustomerNotificationEmail();
    }
}
