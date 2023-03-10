<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */

namespace Extendware\FreeGift\Model\Rule\Action\Discount;

use Magento\Checkout\Model\Session as checkoutSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount;
use Magento\SalesRule\Model\Rule\Action\Discount\Data as DiscountData;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;
use Magento\SalesRule\Api\Data\RuleInterface;
use Extendware\FreeGift\Api\Data\GiftRuleInterface;
use Extendware\FreeGift\Api\GiftRuleRepositoryInterface;
use Extendware\FreeGift\Helper\Cache as GiftRuleCacheHelper;
use Extendware\FreeGift\Model\GiftRule;

/**
 * Class Offer Product Per Price Range
 *
 * @copyright 2020 Extendware
 */
class OfferProductPerPriceRange extends AbstractDiscount
{
    /**
     * @var checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var GiftRuleCacheHelper
     */
    protected $giftRuleCacheHelper;

    /**
     * @var GiftRuleRepositoryInterface
     */
    protected $giftRuleRepository;

    /**
     * OfferProductPerPriceRange constructor.
     *
     * @param Validator                   $validator           Validator
     * @param DataFactory                 $discountDataFactory Discount data factory
     * @param PriceCurrencyInterface      $priceCurrency       Price currency
     * @param checkoutSession             $checkoutSession     Checkout session
     * @param GiftRuleCacheHelper         $giftRuleCacheHelper Gift rule cache helper
     * @param GiftRuleRepositoryInterface $giftRuleRepository  Gift rule repository
     */
    public function __construct(
        Validator $validator,
        DataFactory $discountDataFactory,
        PriceCurrencyInterface $priceCurrency,
        checkoutSession $checkoutSession,
        GiftRuleCacheHelper $giftRuleCacheHelper,
        GiftRuleRepositoryInterface $giftRuleRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->giftRuleCacheHelper = $giftRuleCacheHelper;
        $this->giftRuleRepository = $giftRuleRepository;

        parent::__construct(
            $validator,
            $discountDataFactory,
            $priceCurrency
        );
    }

    /**
     * @param Rule         $rule Rule
     * @param AbstractItem $item Item
     * @param float        $qty  Qty
     *
     * @return DiscountData
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $item->getQuote();

        $calculateId = 'calculate_gift_rule_'.$rule->getRuleId();
        if (!$quote->getData($calculateId)) {
            // Set only for performance (not save in DB).
            $quote->setData($calculateId, true);

            /**
             * Rules load by collection => extension attributes not present in rule entity
             */
            /** @var GiftRule $giftRule */
            $giftRule = $this->giftRuleRepository->getById($rule->getRuleId());

            if ((float)$giftRule->getPriceRange() && $quote->getGrandTotal() >= $giftRule->getPriceRange()) {
                /** @var int $level */
                $range = floor($quote->getGrandTotal() / $giftRule->getPriceRange());

                // Save active gift rule in session.
                $giftRuleSessionData = $this->checkoutSession->getGiftRules();
                $giftRuleSessionData[$rule->getRuleId()] = $rule->getRuleId() . '_' . $range;
                $this->checkoutSession->setGiftRules($giftRuleSessionData);

                // Set number offered product.
                $giftRule->setNumberOfferedProduct($giftRule->getMaximumNumberProduct() * $range);

                $this->giftRuleCacheHelper->saveCachedGiftRule(
                    $rule->getRuleId() . '_' . $range,
                    $rule,
                    $giftRule
                );
            } else {
                // Save active gift rule in session.
                $giftRuleSessionData = $this->checkoutSession->getGiftRules();
                if (isset($giftRuleSessionData[$rule->getRuleId()])) {
                    unset($giftRuleSessionData[$rule->getRuleId()]);
                }
                $this->checkoutSession->setGiftRules($giftRuleSessionData);
            }
        }

        return $discountData;
    }
}
