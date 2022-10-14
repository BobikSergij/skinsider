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
use Extendware\FreeGift\Api\GiftRuleRepositoryInterface;
use Extendware\FreeGift\Helper\Cache as GiftRuleCacheHelper;
use Extendware\FreeGift\Model\GiftRule;

/**
 * Class OfferProduct
 *
 * @copyright 2020 Extendware
 */
class OfferProduct extends AbstractDiscount
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
     * OfferProduct constructor.
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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

            /** @var GiftRule $giftRule */
            $giftRule = $this->giftRuleRepository->getById($rule->getRuleId());

            // Set number offered product.
            $giftRule->setNumberOfferedProduct($giftRule->getMaximumNumberProduct());

            // Save active gift rule in session.
            $giftRuleSessionData = $this->checkoutSession->getGiftRules();
            $giftRuleSessionData[$rule->getRuleId()] = $rule->getRuleId();
            $this->checkoutSession->setGiftRules($giftRuleSessionData);

            $this->giftRuleCacheHelper->saveCachedGiftRule(
                $rule->getRuleId(),
                $rule,
                $giftRule
            );
        }

        return $discountData;
    }
}
