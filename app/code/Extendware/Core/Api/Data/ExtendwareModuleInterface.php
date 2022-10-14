<?php

namespace Extendware\Core\Api\Data;

interface ExtendwareModuleInterface
{
    const MODULE_ID='module_id';
    const MODULE_NAME='module_name';
    const LICENSE_KEY='license_key';
    const MODULE_ACTIVE ='module_active';

    /**
     * @return int
     */
    public function getModuleId();

    /**
     * @param int $moduleId
     *
     * @return ExtendwareModuleInterface
     */
    public function setModuleId($moduleId);

    /**
     * @return string
     */
    public function getModuleName();

    /**
     * @param string $moduleName
     *
     * @return ExtendwareModuleInterface
     */
    public function setModuleName($moduleName);

    /**
     * @return string
     */
    public function getLicenseKey();

    /**
     * @param string $licenseKey
     *
     * @return ExtendwareModuleInterface
     */
    public function setLicenseKey($licenseKey);

    /**
     * @return bool
     */
    public function getModuleActive();

    /**
     * @param bool $moduleActive
     *
     * @return ExtendwareModuleInterface
     */
    public function setModuleActive($moduleActive);
}
