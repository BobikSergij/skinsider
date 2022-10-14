<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */
namespace Extendware\FreeGift\Model\ResourceModel\GiftRule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Extendware\FreeGift\Api\Data\GiftRuleInterface;

/**
 * GiftRule collection.
 *
 * @copyright 2020 Extendware
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray(GiftRuleInterface::RULE_ID, GiftRuleInterface::RULE_ID);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            \Extendware\FreeGift\Model\GiftRule::class,
            \Extendware\FreeGift\Model\ResourceModel\GiftRule::class
        );
    }
}
