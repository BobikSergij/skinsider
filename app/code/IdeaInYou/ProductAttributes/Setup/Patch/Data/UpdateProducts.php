<?php
/**
 * IdeaInYou_ProductAttributes
 *
 * @copyright   Copyright (c) 2022 IdeaInYou Company (www.ideainyou.com)
 * @author      Ruslan Pundyk <ruslan.p@ideainyou.com>
 */
namespace IdeaInYou\ProductAttributes\Setup\Patch\Data;

use IdeaInYou\ProductAttributes\Model\Category;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Data Patch update all products some Attributes value as same SubCategories
 */
class UpdateProducts implements DataPatchInterface
{
    /**
     * @var Category
     */
    private Category $category;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var State
     */
    private State $state;

    /**
     * @param Category $category
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param State $state
     * @param LoggerInterface $logger
     */
    public function __construct(
        Category $category,
        ModuleDataSetupInterface $moduleDataSetup,
        State $state,
        LoggerInterface $logger
    ) {
        $this->category = $category;
        $this->logger = $logger;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->state = $state;
    }

    /**
     * @return UpdateProducts|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->state->setAreaCode(Area::AREA_ADMINHTML);

        $productCollection = $this->category->getProductCollection();
        $products = $productCollection->getItems();
        $subCategoryIds = $this->category->getSubCategoryIds();
        foreach ($products as $product) {
            $productCategoryIds = $this->category->getProductAllCategoryIds($product);
            $categoryNameArr = [];
            foreach ($subCategoryIds as $subCategoryId) {
                if (in_array($subCategoryId, $productCategoryIds)) {
                    $parentCategoryName = $this->category->getParentCategoryName($subCategoryId);
                    $attributeCodeItems = $this->category->getAttrCodeByName($parentCategoryName)->getItems();
                    $attributeCode = array_values($attributeCodeItems)[0]->getAttributeCode();
                    $attr = $product->getResource()->getAttribute($attributeCode);
                    $subCategoryName = $this->category->getCategoryNameById($subCategoryId);
                    $optionId = $attr->getSource()->getOptionId($subCategoryName);
                    $categoryNameArr[$attributeCode][] = $optionId;
                }
            }
            foreach ($categoryNameArr as $index => $arr) {
                $value = implode(",", $arr);
                $product->setData($index, $value);
            }
        }
        try {
            $productCollection->save();
        } catch (\Exception $e) {
            $this->logger->error(
                $e->getMessage(),
                [
                    'method' => __METHOD__
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [UpdateProductAttrs::class];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
