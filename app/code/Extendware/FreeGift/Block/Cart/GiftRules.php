<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */
namespace Extendware\FreeGift\Block\Cart;

use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\View\Element\Template\Context;
use Extendware\FreeGift\Api\GiftRuleServiceInterface;

/**
 * Class GiftRules
 *
 * @copyright 2020 Extendware
 */
class GiftRules extends \Magento\Framework\View\Element\Template
{
    /**
     * @var GiftRuleServiceInterface
     */
    protected $giftRuleService;

    /**
     * @var CheckoutCart
     */
    protected $cart;

    /**
     * Cart constructor.
     *
     * @param Context                  $context         Context
     * @param GiftRuleServiceInterface $giftRuleService Gift rule service
     * @param CheckoutCart             $cart            Cart
     * @param array                    $data            Data
     */
    public function __construct(
        Context $context,
        GiftRuleServiceInterface $giftRuleService,
        CheckoutCart $cart,
        array $data = []
    ) {
        $this->giftRuleService = $giftRuleService;
        $this->cart = $cart;
        parent::__construct($context, $data);
    }

    /**
     * Get gift rules
     *
     * @return array
     */
    public function getGiftRules()
    {
        return $this->giftRuleService->getAvailableGifts($this->cart->getQuote());
    }
}
