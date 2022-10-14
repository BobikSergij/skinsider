<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace IdeaInYou\StoreName\ViewModel;

class StoreName extends \Magento\Framework\View\Element\Template
{
    protected $_storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Get Store name
     *
     * @return string
     */
    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }
}
