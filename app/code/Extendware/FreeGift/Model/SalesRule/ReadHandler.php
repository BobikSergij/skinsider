<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2019 Extendware
 */

namespace Extendware\FreeGift\Model\SalesRule;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Model\Rule;
use Extendware\FreeGift\Api\Data\GiftRuleInterface;
use Extendware\FreeGift\Model\GiftRuleRepository;

/**
 * Class ReadHandler
 *
 * @copyright 2019 Extendware
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var GiftRuleRepository
     */
    protected $giftRuleRepository;

    /**
     * ReadHandler constructor.
     *
     * @param MetadataPool       $metadataPool       Metadata pool
     * @param GiftRuleRepository $giftRuleRepository Gift rule repository
     */
    public function __construct(
        MetadataPool $metadataPool,
        GiftRuleRepository $giftRuleRepository
    ) {
        $this->metadataPool = $metadataPool;
        $this->giftRuleRepository = $giftRuleRepository;
    }

    /**
     * Fill Sales Rule extension attributes with gift rule attributes
     *
     * @param Rule|object $entity    Entity
     * @param array       $arguments Arguments
     *
     * @return Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $attributes = $entity->getExtensionAttributes() ?: [];
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        if ($entity->getData($metadata->getLinkField())) {
            try {
                /** @var GiftRuleInterface $giftRule */
                $giftRule = $this->giftRuleRepository->getById($entity->getData($metadata->getLinkField()));

                $maximumNumber = $giftRule->getMaximumNumberProduct();
                $attributes['gift_rule'][GiftRuleInterface::MAXIMUM_NUMBER_PRODUCT] = $maximumNumber;
                $attributes['gift_rule'][GiftRuleInterface::AVAILABLE_SKU] = $giftRule->getAvailableSku();
                $attributes['gift_rule'][GiftRuleInterface::PRICE_RANGE] = $giftRule->getPriceRange();
            } catch (NoSuchEntityException $exception) {
                $attributes['gift_rule'][GiftRuleInterface::MAXIMUM_NUMBER_PRODUCT] = null;
                $attributes['gift_rule'][GiftRuleInterface::AVAILABLE_SKU] = null;
                $attributes['gift_rule'][GiftRuleInterface::PRICE_RANGE] = null;
            }
        }
        $entity->setExtensionAttributes($attributes);

        return $entity;
    }
}
