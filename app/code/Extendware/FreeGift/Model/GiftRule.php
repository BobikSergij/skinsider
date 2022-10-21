<?php
/**
 *
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */
namespace Extendware\FreeGift\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Extendware\FreeGift\Api\Data\GiftRuleInterface;

/**
 * GiftRule model.
 *
 * @method int getNumberOfferedProduct()
 * @method GiftRule setNumberOfferedProduct(int $number)
 */
class GiftRule extends AbstractModel implements GiftRuleInterface, IdentityInterface
{
    const CACHE_TAG = 'extendware_gift_sales_rule_gift_rule';

    /**
     * @var string
     */
    protected $_eventPrefix = 'extendware_gift_sales_rule_gift_rule';

    /**
     * @var string
     */
    protected $_eventObject = 'extendware_gift_sales_rule_gift_rule';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    public function getMaximumNumberProduct()
    {
        return $this->getData(self::MAXIMUM_NUMBER_PRODUCT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMaximumNumberProduct($maximumNumberProduct)
    {
        return $this->setData(self::MAXIMUM_NUMBER_PRODUCT, $maximumNumberProduct);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceRange()
    {
        return $this->getData(self::PRICE_RANGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriceRange($priceRange)
    {
        return $this->setData(self::PRICE_RANGE, $priceRange);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvaiableSku()
    {
        return $this->getData(self::AVAILABLE_SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function setAvaiableSku($availableSku)
    {
        return $this->setData(self::AVAILABLE_SKU, $availableSku);
    }

    /**
     * Populate the object from array values
     * It is better to use setters instead of the generic setData method
     *
     * @param array $values values
     *
     * @return GiftRule
     */
    public function populateFromArray(array $values)
    {
        $this->setMaximumNumberProduct($values['maximum_number_product']);
        $this->setAvailableProductSku($values['available_sku']);
        $this->setPriceRange($values['price_range']);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            \Extendware\FreeGift\Model\ResourceModel\GiftRule::class
        );
    }
}
