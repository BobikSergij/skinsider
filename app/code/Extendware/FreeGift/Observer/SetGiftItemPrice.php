<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */
namespace Extendware\FreeGift\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SetGiftItemPrice
 *
 * @copyright 2020 Extendware
 */
class SetGiftItemPrice implements ObserverInterface
{
    /**
     * Change price for gift product
     *
     * @param Observer $observer Observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getEvent()->getData('quote_item');
        if ($item->getOptionByCode('option_gift_rule') !== null) {
            $item->setCustomPrice(0);
            $item->setOriginalCustomPrice(0);
        }
    }
}
