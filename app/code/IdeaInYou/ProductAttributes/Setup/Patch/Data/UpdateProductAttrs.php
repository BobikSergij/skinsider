<?php
/**
 * IdeaInYou_ProductAttributes
 *
 * @copyright   Copyright (c) 2022 IdeaInYou Company (www.ideainyou.com)
 * @author      Ruslan Pundyk <ruslan.p@ideainyou.com>
 */
namespace IdeaInYou\ProductAttributes\Setup\Patch\Data;

use IdeaInYou\ProductAttributes\Api\AttributeActionInterface;
use IdeaInYou\ProductAttributes\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Data Patch remove and create some attributes
 */
class UpdateProductAttrs implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Category
     */
    private Category $category;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Category $category
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Category $category,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->logger = $logger;
        $this->category = $category;
    }

    /**
     * @return DataPatchInterface|void
     *
     * @throws \Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        try {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            foreach (AttributeActionInterface::ATTR_ARR as $key => $value) {
                $eavSetup->removeAttribute(Product::ENTITY, $key);
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    $key,
                    [
                        'type' => 'text',
                        'label' => $value,
                        'input' => 'multiselect',
                        'required' => false,
                        'user_defined' => true,
                        'sort_order' => 500,
                        'visible' => true,
                        'filterable' => true,
                        'visible_on_front' => true,
                        'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                        'backend' => ArrayBackend::class,
                        'option' => [
                            'values' => $this->category->getSubCategories($value)
                        ]
                    ]
                );

                $eavSetup->addAttributeToGroup(
                    Product::ENTITY,
                    AttributeActionInterface::ATTR_SET,
                    AttributeActionInterface::ATTR_GROUP,
                    $key
                );
            }
        } catch (\Exception $e) {
            $this->logger->error(
                $e->getMessage(),
                [
                    'method' => __METHOD__,
                    'attribute_code' => $key
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [UpdateIngrediensAttr::class];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
