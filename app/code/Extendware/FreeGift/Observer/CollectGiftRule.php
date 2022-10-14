<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */

namespace Extendware\FreeGift\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Extendware\FreeGift\Helper\Cache as GiftRuleCacheHelper;
use Extendware\FreeGift\Helper\Config as GiftRuleConfigHelper;
use Extendware\FreeGift\Api\GiftRuleServiceInterface;

/**
 * Class CollectGiftRule
 *
 * @copyright 2020 Extendware
 */
class CollectGiftRule implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var GiftRuleServiceInterface
     */
    protected $giftRuleService;

    /**
     * @var GiftRuleCacheHelper
     */
    protected $giftRuleCacheHelper;

    /**
     * @var GiftRuleConfigHelper
     */
    protected $giftRuleConfigHelper;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Http
     */
    protected $request;

    /**
     * CollectGiftRule constructor.
     *
     * @param CheckoutSession          $checkoutSession      Checkout session
     * @param GiftRuleServiceInterface $giftRuleService      Gift rule service
     * @param GiftRuleCacheHelper      $giftRuleCacheHelper  Gift rule cache helper
     * @param GiftRuleConfigHelper     $giftRuleConfigHelper Gift rule config helper
     * @param CartRepositoryInterface  $quoteRepository      Quote repository
     * @param Http                     $request              Request
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        GiftRuleServiceInterface $giftRuleService,
        GiftRuleCacheHelper $giftRuleCacheHelper,
        GiftRuleConfigHelper $giftRuleConfigHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->giftRuleService = $giftRuleService;
        $this->giftRuleCacheHelper = $giftRuleCacheHelper;
        $this->giftRuleConfigHelper = $giftRuleConfigHelper;
        $this->quoteRepository = $quoteRepository;
        $this->request = $request;
    }

    /**
     * Update gift item quantity
     * Add automatic gift item
     *
     * @param Observer $observer Oberver
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        /** @var array $giftRules */
        $giftRules = $this->checkoutSession->getGiftRules();

        if ($giftRules) {
            /** @var Quote $quote */
            $quote = $observer->getEvent()->getQuote();

            /** @var array $ruleIds */
            $ruleIds = explode(',', $quote->getAppliedRuleIds());

            $saveQuote = false;

            /** @var array $newGiftRulesList */
            $newGiftRulesList = [];
            foreach ($giftRules as $giftRuleId => $giftRuleCode) {
                if (!in_array($giftRuleId, $ruleIds)) {
                    /** @var Item $item */
                    foreach ($quote->getAllItems() as $item) {
                        $option = $item->getOptionByCode('option_gift_rule');
                        if ($option && $option->getValue() == $giftRuleId) {
                            // Remove gift item.
                            $quote->deleteItem($item);
                            $saveQuote = true;
                        }
                    }
                } else {
                    $newGiftRulesList[$giftRuleId] = $giftRuleCode;
                    $giftItem    = [];
                    $giftItemQty = 0;

                    /** @var Item $item */
                    foreach ($quote->getAllItems() as $item) {
                        /** @var Option $option */
                        $option = $item->getOptionByCode('option_gift_rule');
                        /** @var Option $configurableOption */
                        $configurableOption = $item->getOptionByCode('simple_product');
                        if ($option && $option->getValue() == $giftRuleId && !$configurableOption) {
                            $giftItem[] = $item;
                            $giftItemQty += $item->getQty();
                        }
                    }

                    $giftRuleData = $this->giftRuleCacheHelper->getCachedGiftRule($giftRuleCode);
                    if (!$giftRuleData) {
                        continue;
                    }

                    // If only 1 gift product available => add automatic gift product.
                    if ($this->giftRuleConfigHelper->isAutomaticAddEnabled() && count($giftItem) == 0 &&
                        count($giftRuleData[GiftRuleCacheHelper::DATA_PRODUCT_ITEMS]) == 1) {
                        $this->giftRuleService->addGiftProducts(
                            $quote,
                            [
                                [
                                    'id' => key($giftRuleData[GiftRuleCacheHelper::DATA_PRODUCT_ITEMS]),
                                    'qty' => $giftRuleData[GiftRuleCacheHelper::DATA_NUMBER_OFFERED_PRODUCT],
                                ],
                            ],
                            $giftRuleCode,
                            $giftRuleId
                        );
                        $saveQuote = true;
                    }

                    if ($giftItemQty > $giftRuleData[GiftRuleCacheHelper::DATA_NUMBER_OFFERED_PRODUCT]) {
                        // Delete gift item.
                        $qtyToDelete = $giftItemQty - $giftRuleData[GiftRuleCacheHelper::DATA_NUMBER_OFFERED_PRODUCT];

                        foreach (array_reverse($giftItem) as $item) {
                            if ($item->getQty() > $qtyToDelete) {
                                $item->setQty($item->getQty() - $qtyToDelete);
                                $saveQuote = true;
                                break;
                            } else {
                                $qtyToDelete = $qtyToDelete - $item->getQty();
                                $parentItemId = $item->getParentItemId();
                                if ($parentItemId) {
                                    $quote->removeItem($parentItemId);
                                }
                                $quote->deleteItem($item);
                                $saveQuote = true;
                            }

                            if ($qtyToDelete == 0) {
                                break;
                            }
                        }
                    }
                }
            }

            /**
             * Save quote if it is not cart add controller and item changed
             */
            if ($saveQuote
                && !($this->request->getControllerName() == 'cart' && $this->request->getActionName() == 'add')) {
                $this->quoteRepository->save($quote);
            }

            $this->checkoutSession->setGiftRules($newGiftRulesList);
        }
    }
}
