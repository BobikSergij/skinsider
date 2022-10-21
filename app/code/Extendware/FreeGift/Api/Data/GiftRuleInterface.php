<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2019 Extendware
 */
namespace Extendware\FreeGift\Api\Data;

/**
 * GiftRule interface.
 *
 * @api
 * @copyright 2020 Extendware
 */
interface GiftRuleInterface
{
    const TABLE_NAME             = 'extendware_gift_salesrule';
    const RULE_ID                = 'rule_id';
    const MAXIMUM_NUMBER_PRODUCT = 'maximum_number_product';
    const AVAILABLE_SKU          = 'available_sku';
    const PRICE_RANGE            = 'price_range';

    /**
     * Rule type actions
     */
    const OFFER_PRODUCT                 = 'offer_product';
    const OFFER_PRODUCT_PER_PRICE_RANGE = 'offer_product_per_price_range';

    /**
     * Get the maximum number product.
     *
     * @return int
     */
    public function getMaximumNumberProduct();

    /**
     * Set the maximum number product.
     *
     * @param int $value Value
     * @return $this
     */
    public function setMaximumNumberProduct($value);

    /**
     * Get the price range.
     *
     * @return float
     */
    public function getPriceRange();

    /**
     * Set the price range.
     *
     * @param decimal $value Value
     * @return $this
     */
    public function setPriceRange($value);

    /**
     * Get the available sku.
     *
     * @return string
     */
    public function getAvaiableSku();

    /**
     * Set the available sku.
     *
     * @param string $value Value
     * @return $this
     */
    public function setAvaiableSku($value);
}
