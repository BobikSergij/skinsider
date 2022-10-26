<?php

namespace IdeaInYou\BigCommerce\Plugin;

use IdeaInYou\BigCommerce\Setup\Patch\Data\BigCommerceIdAttribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class BigCommerceId
{
    const SALES_ORDER_TABLE = 'sales_order';
    /**
     * @param Collection $subject
     * @return null
     * @throws LocalizedException
     */
    public function beforeLoad(Collection $subject)
    {
        if (!$subject->isLoaded()) {
            $joinTable = $subject->getTable('sales_order');
            $subject->getSelect()->joinLeft(
                $joinTable,
                'main_table.entity_id = ' . self::SALES_ORDER_TABLE . '.' . OrderInterface::ENTITY_ID,
                [
                    BigCommerceIdAttribute::BIG_COMMERCE_ID,
                ]);
        }

        return null;
    }
}
