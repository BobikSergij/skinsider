<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2019 Extendware
 */
namespace Extendware\FreeGift\Plugin\Checkout\Model;

use Magento\Checkout\Model\Cart as Subject;
use Magento\Sales\Model\Order\Item;

/**
 * Class Cart
 *
 * @copyright 2019 Extendware
 */
class Cart
{
    /**
     * Avoid to add a gift product when adding an order item.
     *
     * @param Subject  $subject   Subject
     * @param \Closure $proceed   Parent method
     * @param Item     $orderItem Order item
     * @param bool     $qtyFlag   Quantity flag
     *
     * @return Subject
     */
    public function aroundAddOrderItem(
        Subject $subject,
        \Closure $proceed,
        $orderItem,
        $qtyFlag = null
    ) {
        $info = $orderItem->getProductOptionByCode('info_buyRequest');
        if (isset($info['gift_rule']) && $info['gift_rule']) {
            return $subject;
        }

        return $proceed($orderItem, $qtyFlag);
    }
}
