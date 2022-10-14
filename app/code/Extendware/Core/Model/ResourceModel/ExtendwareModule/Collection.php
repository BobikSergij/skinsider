<?php

namespace Extendware\Core\Model\ResourceModel\ExtendwareModule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName='module_id';

    /**
     * @var string
     */
    protected $_eventPrefix='extendware_core_module_collection';

    /**
     * @var string
     */
    protected $_eventObject='module_collection_save_before';

    protected function _construct()
    {
        $this->_init(\Extendware\Core\Model\ExtendwareModule::class, \Extendware\Core\Model\ResourceModel\ExtendwareModule::class);
    }
}
