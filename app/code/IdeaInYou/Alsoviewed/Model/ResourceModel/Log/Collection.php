<?php

namespace IdeaInYou\Alsoviewed\Model\ResourceModel\Log;

class Collection extends \IdeaInYou\Alsoviewed\Model\ResourceModel\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magento\Framework\DataObject', 'IdeaInYou\Alsoviewed\Model\ResourceModel\Log');
    }
}
