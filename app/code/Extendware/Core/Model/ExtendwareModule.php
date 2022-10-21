<?php

namespace Extendware\Core\Model;

use Extendware\Core\Api\Data\ExtendwareModuleInterface;
use Extendware\Core\Model\ResourceModel\ExtendwareModule as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class ExtendwareModule extends AbstractModel implements ExtendwareModuleInterface
{
    const CACHE_TAG = 'extendware_core_module';

    /**
     * @var string
     */
    protected $_cacheTag = 'extendware_core_module';

    /**
     * @var string
     */
    protected $_eventPrefix = 'extendware_core_module';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getModuleId()
    {
        return $this->getData(ExtendwareModuleInterface::MODULE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setModuleId($moduleId)
    {
        return $this->setData(ExtendwareModuleInterface::MODULE_ID, $moduleId);
    }

    /**
     * @inheritDoc
     */
    public function getModuleName()
    {
        return $this->getData(ExtendwareModuleInterface::MODULE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setModuleName($moduleName)
    {
        return $this->setData(ExtendwareModuleInterface::MODULE_NAME, $moduleName);
    }

    /**
     * @inheritDoc
     */
    public function getLicenseKey()
    {
        return $this->getData(ExtendwareModuleInterface::LICENSE_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setLicenseKey($licenseKey)
    {
        return $this->setData(ExtendwareModuleInterface::LICENSE_KEY, $licenseKey);
    }

    /**
     * @inheritDoc
     */
    public function getModuleActive()
    {
        return $this->getData(ExtendwareModuleInterface::MODULE_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function setModuleActive($moduleActive)
    {
        return $this->setData(ExtendwareModuleInterface::MODULE_ACTIVE, $moduleActive);
    }
}
