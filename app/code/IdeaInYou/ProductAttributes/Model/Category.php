<?php
/**
 * IdeaInYou_ProductAttributes
 *
 * @copyright   Copyright (c) 2022 IdeaInYou Company (www.ideainyou.com)
 * @author      Ruslan Pundyk <ruslan.p@ideainyou.com>
 */
namespace IdeaInYou\ProductAttributes\Model;

use IdeaInYou\ProductAttributes\Api\AttributeActionInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as EavAttributeCollection;
use Magento\Framework\DataObject;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class
 */
class Category implements AttributeActionInterface
{
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $categoryCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    private ProductCollectionFactory $productCollectionFactory;

    /**
     * @var CategoryFactory
     */
    private CategoryFactory $categoryFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var AttributeCollectionFactory
     */
    private AttributeCollectionFactory $attributeCollectionFactory;

    /**
     * @param CategoryRepository $categoryRepository
     * @param CollectionFactory $categoryCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CategoryFactory $categoryFactory
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        CollectionFactory $categoryCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        CategoryFactory $categoryFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->logger = $logger;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @param $categoryName
     * @return array
     * @throws LocalizedException
     */
    public function getSubCategories($categoryName): array
    {
        $categories = $this->getAllCategories();
        $subCategoriesArr = [];
        foreach ($categories as $category) {
            if ($category->getName() === $categoryName) {
                $subCategories = $category->getChildrenCategories()->getItems();
                foreach ($subCategories as $subCategory) {
                    $subCategoriesArr[] = $subCategory->getName();
                }
            }
        }

        return $subCategoriesArr;
    }

    /**
     * @return array
     */
    public function getSubCategoryIds()
    {
        $subCategoryIds = [];
        foreach (AttributeActionInterface::CATEGORY_ARR as $categoryName) {
            $collection = $this->categoryFactory->create();
            $categoryColl = $collection->getCollection()->addAttributeToFilter('name', $categoryName)->getItems();
            foreach ($categoryColl as $item) {
                $subCategories = $item->getChildrenCategories()->getItems();

                foreach ($subCategories as $subCategory) {
                    $subCategoryIds[] = $subCategory->getId();
                }
            }
        }

        return $subCategoryIds;
    }

    /**
     * @return Collection
     */
    public function getProductCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')->load();

        return $collection;
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getProductAllCategoryIds($product)
    {
        return $product->getCategoryIds();
    }

    /**
     * @return array|DataObject[]
     */
    public function getAllCategories(): array
    {
        $categories = $this->categoryCollectionFactory->create();
        $collection = $categories->addAttributeToSelect('*');

        return $collection->getItems();
    }

    /**
     * @param $categoryId
     * @return string|null
     */
    public function getParentCategoryName($categoryId)
    {
        try {
            $category = $this->categoryRepository->get($categoryId);
            $parentCategoryId = $category->getParentId();
            $parentCategoryName = $this->getCategoryNameById($parentCategoryId);
        } catch (\Exception $e) {
            $this->logger->error(
                $e->getMessage(),
                [
                    'category_id' => $categoryId
                ]
            );
        }

        return $parentCategoryName;
    }

    /**
     * @param $categoryId
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getCategoryNameById($categoryId)
    {
        $category = $this->categoryRepository->get($categoryId);

        return $category->getName();
    }

    /**
     * @param $attrName
     * @return EavAttributeCollection
     */
    public function getAttrCodeByName($attrName)
    {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter('frontend_label', $attrName);

        return $collection;
    }
}
