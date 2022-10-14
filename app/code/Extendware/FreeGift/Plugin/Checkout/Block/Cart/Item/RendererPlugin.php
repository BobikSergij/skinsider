<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */
namespace Extendware\FreeGift\Plugin\Checkout\Block\Cart\Item;

use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Class CombinePlugin
 *
 * @copyright 2020 Extendware
 */
class RendererPlugin
{
    /**
     * @var array
     */
    protected $actionsBlockToRemove = [
        'checkout.cart.item.renderers.default.actions.edit',
        'checkout.cart.item.renderers.simple.actions.edit',
        'checkout.cart.item.renderers.configurable.actions.edit',
    ];

    /**
     * Remove the edit action from the item renderer for gift items.
     *
     * @param Renderer     $subject Subject
     * @param AbstractItem $item    Item
     *
     * @return array
     */
    public function beforeGetActions(
        Renderer $subject,
        AbstractItem $item
    ) {
        $option = $item->getOptionByCode('option_gift_rule');
        if ($option) {
            $actionsBlock = $subject->getChildBlock('actions');
            if ($actionsBlock) {
                foreach ($this->actionsBlockToRemove as $blockName) {
                    if ($actionsBlock->getChildBlock($blockName)) {
                        $actionsBlock->unsetChild($blockName);
                    }
                }
            }
        }

        return [$item];
    }
}
