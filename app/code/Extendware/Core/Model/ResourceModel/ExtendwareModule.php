<?php

namespace Extendware\Core\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ExtendwareModule extends AbstractDb
{
    const TABLE_NAME='extendware_module';
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, \Extendware\Core\Api\Data\ExtendwareModuleInterface::MODULE_ID);
    }
}
