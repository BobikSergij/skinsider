<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */
namespace Extendware\FreeGift\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * GiftRule search results data interface.
 *
 * @api
 * @copyright 2020 Extendware
 */
interface GiftRuleSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get giftrule items.
     *
     * @return \Extendware\FreeGift\Api\Data\GiftRuleInterface
     */
    public function getItems();

    /**
     * Set giftrule items.
     *
     * @param GiftRuleInterface $items Gift rule interface
     * @return $this
     */
    public function setItems(array $items);
}
