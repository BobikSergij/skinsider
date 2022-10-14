<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */
namespace Extendware\FreeGift\Block\Cart\GiftRules;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Extendware\FreeGift\Api\Data\GiftRuleDataInterface;
use Extendware\FreeGift\Api\GiftRuleServiceInterface;
use Extendware\FreeGift\Helper\GiftRule as GiftRuleHelper;
use Extendware\FreeGift\Helper\Config as GiftRuleConfigHelper;

/**
 * Class GiftRules
 *
 * @copyright 2020 Extendware
 */
class Rule extends \Magento\Framework\View\Element\Template
{
    /**
     * @var GiftRuleServiceInterface
     */
    protected $giftRuleService;

    /**
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * @var GiftRuleConfigHelper
     */
    protected $giftRuleConfigHelper;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Cart constructor.
     *
     * @param Context                  $context           Context
     * @param GiftRuleServiceInterface $giftRuleService   Gift rule service
     * @param GiftRuleHelper           $giftRuleHelper    Gift rule helper
     * @param CollectionFactory        $collectionFactory Collection factory
     * @param array                    $data              Data
     */
    public function __construct(
        Context $context,
        GiftRuleServiceInterface $giftRuleService,
        GiftRuleHelper $giftRuleHelper,
        GiftRuleConfigHelper $giftRuleConfigHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->giftRuleService = $giftRuleService;
        $this->giftRuleHelper = $giftRuleHelper;
        $this->giftRuleConfigHelper = $giftRuleConfigHelper;
        $this->productCollectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get gift rule
     *
     * @return GiftRuleDataInterface
     */
    public function getGiftRule(): GiftRuleDataInterface
    {
        return $this->getData('gift_rule');
    }

    public function getGiftLabel()
    {
        return $this->giftRuleConfigHelper->giftProducLabel();
    }

    /**
     * Set gift rule
     *
     * @param GiftRuleDataInterface $giftRule Gift rule
     *
     * @return $this
     */
    public function setGiftRule(GiftRuleDataInterface $giftRule)
    {
        return $this->setData('gift_rule', $giftRule);
    }

    /**
     * Get product collection.
     *
     * @param array $productItems Product items
     *
     * @return Collection
     */
    public function getProductCollection(array $productItems)
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->addAttributeToSelect(['small_image', 'name'])
            ->addIdFilter(array_keys($productItems))
            ->addFinalPrice()
        ;

        return $productCollection;
    }

    /**
     * Get add to cart url.
     *
     * @param int    $giftRuleId   Gift rule id
     * @param string $giftRuleCode Gift rule code
     *
     * @return string
     */
    public function getAddToCartUrl($giftRuleId, $giftRuleCode)
    {
        return $this->giftRuleHelper->getAddUrl($giftRuleId, $giftRuleCode);
    }

    /**
     * Get button label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getButtonLabel()
    {
        $rule = $this->getGiftRule();
        $buttonLabel = __('Choose my gift(s)');
        if ($rule->getRestNumber() !== $rule->getNumberOfferedProduct()) {
            $buttonLabel = __('Edit my choices');
        }

        return $buttonLabel;
    }
}
