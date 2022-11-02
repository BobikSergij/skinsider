<?php

namespace IdeaInYou\BigCommerce\Plugin;

use MiraklSeller\Api\Model\Connection;

class AcceptAllOrder
{
    /**
     * @param \MiraklSeller\Api\Helper\Order $subject
     * @param Connection $connection
     * @param $orderId
     * @param array $orderLines
     * @return bool
     */
    public function beforeAcceptOrder(\MiraklSeller\Api\Helper\Order $subject,
                                      Connection                     $connection, $orderId, array $orderLines)
    {
        return $orderLines['accepted'] = true;
    }
}
