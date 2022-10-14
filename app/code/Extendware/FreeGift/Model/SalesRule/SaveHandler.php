<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */
namespace Extendware\FreeGift\Model\SalesRule;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Model\Rule;
use Extendware\FreeGift\Api\Data\GiftRuleInterface;
use Extendware\FreeGift\Model\GiftRuleFactory;
use Extendware\FreeGift\Model\GiftRuleRepository;

/**
 * Class SaveHandler
 *
 * @copyright 2020 Extendware
 */
class SaveHandler implements ExtensionInterface
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
     * @var GiftRuleFactory
     */
    protected $giftRuleFactory;

    /**
     * ReadHandler constructor.
     *
     * @param MetadataPool       $metadataPool       Metadata pool
     * @param GiftRuleRepository $giftRuleRepository Gift rule repository
     * @param GiftRuleFactory    $giftRuleFactory    Gift rule factory
     */
    public function __construct(
        MetadataPool $metadataPool,
        GiftRuleRepository $giftRuleRepository,
        GiftRuleFactory $giftRuleFactory
    ) {
        $this->metadataPool = $metadataPool;
        $this->giftRuleRepository = $giftRuleRepository;
        $this->giftRuleFactory = $giftRuleFactory;
    }

    /**
     * Save gift rule value from Sales Rule extension attributes
     *
     * @param object $entity    Entity
     * @param array  $arguments Arguments
     *
     * @return bool|object
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $ruleId = $entity->getData($metadata->getLinkField());
        if ($ruleId) {
            $extensionAttributes = $entity->getExtensionAttributes();
            if (isset($extensionAttributes['gift_rule'])) {
                try {
                    /** @var GiftRuleInterface $giftRule */
                    $giftRule = $this->giftRuleRepository->getById($ruleId);
                } catch (NoSuchEntityException $exception) {
                    // Create gift rule if not exist.
                    $giftRule = $this->giftRuleFactory->create();
                    $giftRule->setId($ruleId);
                }

                $giftRule->addData($extensionAttributes['gift_rule']);

                $this->giftRuleRepository->save($giftRule);
            }
        }

        return $entity;
    }
}
