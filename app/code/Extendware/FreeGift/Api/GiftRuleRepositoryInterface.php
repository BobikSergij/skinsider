<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */
namespace Extendware\FreeGift\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Extendware\FreeGift\Api\Data\GiftRuleInterface;
use Extendware\FreeGift\Api\Data\GiftRuleSearchResultsInterface;

/**
 * GiftRule repository interface.
 *
 * @api
 * @copyright 2020 Extendware
 */
interface GiftRuleRepositoryInterface
{  
    /**
     * Get a giftrule by ID.
     *
     * @param int $entityId Entity id
     * @return \Extendware\FreeGift\Api\Data\GiftRuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Get the giftrules matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria Search criteria
     * @return \Extendware\FreeGift\Api\Data\GiftRuleSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null);

    /**
     * Save the GiftRule.
     *
     * @param GiftRuleInterface $giftRule Gift rule
     * @return \Extendware\FreeGift\Api\Data\GiftRuleInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(GiftRuleInterface $giftRule);

    /**
     * Delete a giftrule by ID.
     *
     * @param int $entityId Entity id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($entityId);
}
