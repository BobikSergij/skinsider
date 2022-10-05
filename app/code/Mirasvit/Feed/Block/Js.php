<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.2.8
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Block;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\Template;

class Js extends Template
{
    /**
     * {@inheritdoc}
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function _toHtml()
    {
        $jsName     = $this->isM245orGreater() ? 'report245' : 'report';
        $initObject = [
            'Mirasvit_Feed/js/' . $jsName => [
            ]
        ];

        return '<div data-mage-init=\'' . json_encode($initObject) . '\'></div>';
    }

    public function isM245orGreater(): bool
    {
        $version = \Mirasvit\Core\Service\CompatibilityService::getVersion();

        return version_compare($version, '2.4.5', '>=');
    }
}
