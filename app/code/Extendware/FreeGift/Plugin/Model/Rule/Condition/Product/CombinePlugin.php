<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2020 Extendware
 */
namespace Extendware\FreeGift\Plugin\Model\Rule\Condition\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine;
use Extendware\FreeGift\Helper\GiftRule as GiftRuleHelper;

/**
 * Class CombinePlugin
 *
 * @copyright 2020 Extendware
 */
class CombinePlugin
{
    /**
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * CombinePlugin constructor.
     *
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     */
    public function __construct(
        GiftRuleHelper $giftRuleHelper
    ) {
        $this->giftRuleHelper = $giftRuleHelper;
    }

    /**
     * Return true if rule is a gift sales rule
     *
     * @param Combine       $subject Subject
     * @param callable      $proceed Proceed
     * @param AbstractModel $model   Model
     *
     * @return bool
     */
    public function aroundValidate(
        Combine $subject,
        callable $proceed,
        AbstractModel $model
    ) {
        if ($this->giftRuleHelper->isGiftRule($subject->getRule())) {
            if (!$this->giftRuleHelper->isValidGiftRule($subject->getRule(), $model->getQuote())) {
                return false;
            }

            return true;
        }

        return $proceed($model);
    }
}
