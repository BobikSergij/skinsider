<?php
/**
 * @category  Extendware
 * @package   Extendware\FreeGift
 * @copyright 2019 Extendware
 */
namespace Extendware\FreeGift\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Helper: Config
 *
 * @copyright 2019 Extendware
 */
class Config extends AbstractHelper
{
    /**#@+
     * Config paths.
     */
    const KEY_CONFIG_AUTOMATIC_ADD = 'freegift/genral/enable';
    /**#@-*/

    /**#@+
     * Config label paths.
     */
    const KEY_CONFIG_LABEL = 'freegift/genral/label';
    /**#@-*/

    /**
     * Get the config value for automatic_add.
     *
     * @return bool
     */
    public function isAutomaticAddEnabled()
    {
        $value = (int) $this->scopeConfig->getValue(self::KEY_CONFIG_AUTOMATIC_ADD);

        return ($value == 1);
    }

    /**
     * Get the config value for automatic_add.
     *
     * @return bool
     */
    public function giftProducLabel()
    {
        $value = $this->scopeConfig->getValue(self::KEY_CONFIG_LABEL);

        return $value;
    }
}
