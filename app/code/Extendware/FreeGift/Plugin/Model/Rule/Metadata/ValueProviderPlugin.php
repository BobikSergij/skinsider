<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */

namespace Extendware\FreeGift\Plugin\Model\Rule\Metadata;

use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Metadata\ValueProvider;
use Extendware\FreeGift\Api\Data\GiftRuleInterface;
use Extendware\FreeGift\Api\GiftRuleRepositoryInterface;
use Extendware\FreeGift\Model\GiftRuleFactory;

/**
 * Add gift sales rule
 *
 * @copyright 2020 Extendware
 */
class ValueProviderPlugin
{
    /**
     * Gift rule repository
     *
     * @var GiftRuleRepositoryInterface
     */
    protected $giftRuleRepository;

    /**
     * Gift rule factory
     *
     * @var GiftRuleFactory
     */
    protected $giftRuleFactory;

    /**
     * UpdateRuleDataObserver constructor.
     *
     * @param GiftRuleRepositoryInterface $giftRuleRepository Gift rule repository
     * @param GiftRuleFactory             $giftRuleFactory    Gift rule factory
     */
    public function __construct(
        ExtensionAttributesFactory $extensionFactory,
        GiftRuleRepositoryInterface $giftRuleRepository,
        GiftRuleFactory $giftRuleFactory
    ) {
        $this->extensionFactory   = $extensionFactory;
        $this->giftRuleRepository = $giftRuleRepository;
        $this->giftRuleFactory    = $giftRuleFactory;
    }

    /**
     * Add gift sales rule label with rule type actions
     *
     * @param ValueProvider $subject Subject
     * @param array         $result  Result
     * @param Rule          $rule    Rule
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMetadataValues(
        ValueProvider $subject,
        $result,
        Rule $rule
    ) {
        try {
            /** @var GiftRuleInterface $giftRule */
            $giftRule = $this->giftRuleRepository->getById($rule->getRuleId());
        } catch (NoSuchEntityException $exception) {
            // Create gift rule if not exist.
            $giftRule = $this->giftRuleFactory->create();
            $giftRule->setId($rule->getRuleId());
        }

        $result['actions']['children']['simple_action']['arguments']['data']['config']['options'][] = [
            'label' => __('To offer product'),
            'value' => GiftRuleInterface::OFFER_PRODUCT,
        ];

        $result['actions']['children']['simple_action']['arguments']['data']['config']['options'][] = [
            'label' => __('To offer product per price range'),
            'value' => GiftRuleInterface::OFFER_PRODUCT_PER_PRICE_RANGE,
        ];

        $result['actions']['children']['maximum_number_product']['arguments']['data']['config'] = [
            'value' => $giftRule->getMaximumNumberProduct(),
        ];

        $result['actions']['children']['available_sku']['arguments']['data']['config'] = [
            'value' => $giftRule->getAvailableSku(),
        ];

        $result['actions']['children']['price_range']['arguments']['data']['config'] = [
            'value' => $giftRule->getPriceRange(),
        ];

        return $result;
    }
}
