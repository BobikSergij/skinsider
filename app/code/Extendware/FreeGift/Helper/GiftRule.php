<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */
namespace Extendware\FreeGift\Helper;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\Rule;
use Extendware\FreeGift\Api\Data\GiftRuleInterface;
use Extendware\FreeGift\Api\GiftRuleRepositoryInterface;

/**
 * Gift rule helper
 *
 * @copyright 2019 Extendware
 */
class GiftRule extends AbstractHelper
{
    /**
     * @var array
     */
    protected $giftRule = [];

    /**
     * @var GiftRuleRepositoryInterface
     */
    protected $giftRuleRepository;

    /**
     * GiftRule constructor.
     *
     * @param Context                     $context            Context
     * @param GiftRuleRepositoryInterface $giftRuleRepository Gift rule repository
     * @param array                       $giftRule           Gift rule
     */
    public function __construct(
        Context $context,
        GiftRuleRepositoryInterface $giftRuleRepository,
        array $giftRule = []
    ) {
        $this->giftRuleRepository = $giftRuleRepository;
        $this->giftRule = $giftRule;

        parent::__construct($context);
    }

    /**
     * Is gift sales rule
     *
     * @param Rule $rule Rule
     *
     * @return bool
     */
    public function isGiftRule(Rule $rule)
    {
        $isGiftRule = false;
        if (in_array($rule->getSimpleAction(), $this->giftRule)) {
            $isGiftRule = true;
        }

        return $isGiftRule;
    }

    /**
     * Check if is valid gift rule for quote
     *
     * @param Rule  $rule  Rule
     * @param Quote $quote Quote
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isValidGiftRule(Rule $rule, Quote $quote)
    {
        $valid = true;

        /**
         * Check if quote has at least one quote item (no gift rule item) in quote
         */
        $hasProduct = false;
        foreach ($quote->getAllItems() as $item) {
            if (!$item->getOptionByCode('option_gift_rule')) {
                $hasProduct = true;
                break;
            }
        }
        if (!$hasProduct) {
            $valid = false;
        }

        if ($valid && $rule->getSimpleAction() == GiftRuleInterface::OFFER_PRODUCT_PER_PRICE_RANGE) {
            /**
             * Rules load by collection => extension attributes not present in rule entity
             */
            /** @var GiftRuleInterface $giftRule */
            $giftRule = $this->giftRuleRepository->getById($rule->getRuleId());

            if ($quote->getGrandTotal() < $giftRule->getPriceRange()) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Retrieve url for add gift product to cart
     *
     * @param int    $giftRuleId   Gift rule id
     * @param string $giftRuleCode Gift rule code
     *
     * @return string
     */
    public function getAddUrl($giftRuleId, $giftRuleCode)
    {
        $routeParams = [
            ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl()),
            'gift_rule_id'   => $giftRuleId,
            'gift_rule_code' => $giftRuleCode,
        ];

        return $this->_getUrl('giftsalesrule/cart/add', $routeParams);
    }
}
