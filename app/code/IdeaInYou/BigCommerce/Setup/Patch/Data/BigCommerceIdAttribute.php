<?php
namespace IdeaInYou\BigCommerce\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Setup\SalesSetupFactory;

class BigCommerceIdAttribute implements DataPatchInterface
{

    const BIG_COMMERCE_ID = "big_commerce_id";
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var SalesSetupFactory
     */
    private SalesSetupFactory $setupFactory;
    private EavSetupFactory $setupFactoryEav;

    /**
     * @param EavSetupFactory $setupFactoryEav
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SalesSetupFactory $setupFactory
     */
    public function __construct(
        EavSetupFactory $setupFactoryEav,
        ModuleDataSetupInterface $moduleDataSetup,
        SalesSetupFactory $setupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->setupFactory = $setupFactory;
        $this->setupFactoryEav = $setupFactoryEav;
    }

    /**
     * @return BigCommerceIdAttribute|void
     */
    public function apply()
    {
        $eavSetup = $this->setupFactoryEav->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(\Magento\Sales\Model\Order::ENTITY, 'big_commerce_id', [
            'type' => 'int',
            'visible' => false,
            'required' => false
        ]);
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getVersion()
    {
        return [];
    }

}
