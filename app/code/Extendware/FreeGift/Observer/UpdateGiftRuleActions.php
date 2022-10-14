<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */
namespace Extendware\FreeGift\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as serializer;
use Magento\SalesRule\Api\Data\RuleInterface;
use Extendware\FreeGift\Helper\GiftRule as GiftRuleHelper;

/**
 * Class UpdateGiftRuleActions
 *
 * @copyright 2020 Extendware
 */
class UpdateGiftRuleActions implements ObserverInterface
{
    const QUOTE_ATTRIBUTE = 'quote_';

    /**
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * @var serializer
     */
    protected $serializer;

    /**
     * UpdateGiftRuleActions constructor.
     *
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     * @param serializer     $serializer     Serializer
     */
    public function __construct(
        GiftRuleHelper $giftRuleHelper,
        serializer $serializer
    ) {
        $this->giftRuleHelper = $giftRuleHelper;
        $this->serializer = $serializer;
    }

    /**
     * Remove quote condition if it's gift rule type
     *
     * @param Observer $observer Observer
     */
    public function execute(Observer $observer)
    {
        /** @var RuleInterface $rule */
        $rule = $observer->getRule();

        if ($this->giftRuleHelper->isGiftRule($rule)) {
            if ($rule->getActions()) {
                $actions = $rule->getActions()->asArray();
                if (isset($actions['conditions'])) {
                    foreach ($actions['conditions'] as $index => $condition) {
                        if (strpos($condition['attribute'], self::QUOTE_ATTRIBUTE) !== false) {
                            // Remove quote condition for gift rule.
                            unset($actions['conditions'][$index]);
                        }
                    }

                    $rule->setActionsSerialized($this->serializer->serialize($actions));
                }
            }
        }
    }
}
