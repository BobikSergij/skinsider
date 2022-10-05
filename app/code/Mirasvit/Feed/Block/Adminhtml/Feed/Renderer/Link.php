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



namespace Mirasvit\Feed\Block\Adminhtml\Feed\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Link extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(DataObject $row)
    {
        /** @var \Mirasvit\Feed\Model\Feed $row */

        $url = $row->getUrl();
        if ($url) {
            return '<a class="feed-link-' . $row->getId() . '" href="' . $url . '" target="_blank">' . $row->getFilename() . '</a>'
                . '<script>document.querySelector(".feed-link-' . $row->getId() . '").addEventListener("click", function(e) { e.stopPropagation();});</script>';
        }

        return $row->getFilename();
    }
}
