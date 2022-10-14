<?php

namespace Extendware\FreeGift\Setup\Patch\Data;

use Extendware\Core\Api\Data\ExtendwareModuleInterfaceFactory;
use Extendware\Core\Api\ExtendwareModuleRepositoryInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class SetDataModule implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var ExtendwareModuleInterfaceFactory
     */
    protected $extendwareModuleFactory;

    /** @var
     * \Extendware\Core\Api\ExtendwareModuleRepositoryInterface
     */
    protected $extendwareModuleRepository;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ExtendwareModuleInterfaceFactory $extendwareModuleFactory
     * @param ExtendwareModuleRepositoryInterface $extendwareModuleRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ExtendwareModuleInterfaceFactory $extendwareModuleFactory,
        ExtendwareModuleRepositoryInterface $extendwareModuleRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->extendwareModuleFactory = $extendwareModuleFactory;
        $this->extendwareModuleRepository = $extendwareModuleRepository;
    }

    /**
     * Do Upgrade
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        /**
         * @var \Extendware\Core\Api\Data\ExtendwareModuleInterface $extendModule
         */
        $extendModule = $this->extendwareModuleFactory->create();
        //MODULE NAME SAME AS THE NAME IN PRODUCT
        $extendModule->setModuleName('Extendware_FreeGift');
        //MODULE ACTIVE STATUS
        $extendModule->setModuleActive(0);
        $this->extendwareModuleRepository->save($extendModule);
        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
