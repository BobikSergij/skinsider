<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2019 Extendware
 */

namespace Extendware\FreeGift\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Model\RegistryConstants;
use Extendware\FreeGift\Helper\GiftRule as GiftRuleHelper;

/**
 * Class PrepareFormActions
 *
 * @copyright 2019 Extendware
 */
class PrepareFormActions implements ObserverInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * PrepareFormActionsObserver constructor.
     *
     * @param Registry        $registry       Registry
     * @param GiftSRuleHelper $giftRuleHelper Gift rule helper
     */
    public function __construct(
        Registry $registry,
        GiftRuleHelper $giftRuleHelper
    ) {
        $this->registry       = $registry;
        $this->giftRuleHelper = $giftRuleHelper;
    }

    /**
     * Change fieldset legend
     *
     * @param Observer $observer Observer
     */
    public function execute(Observer $observer)
    {
        if ($this->giftRuleHelper->isGiftRule($this->getCurrentSalesRule())) {
            /** @var \Magento\Framework\Data\Form $form */
            $form = $observer->getData('form');
            /** @var \Magento\Framework\Data\Form\Element\Fieldset $element */
            $fieldset = $form->getElement('actions_fieldset');

            $fieldset->setData('legend', __('Select free product:'));
        }
    }

    /**
     * Get current sales rule
     *
     * @return RuleInterface
     */
    protected function getCurrentSalesRule()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_SALES_RULE);
    }
}
