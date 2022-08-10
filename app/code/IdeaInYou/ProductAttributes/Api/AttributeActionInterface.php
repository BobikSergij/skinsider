<?php

namespace IdeaInYou\ProductAttributes\Api;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

interface AttributeActionInterface
{
    const SKIN_TYPES = 'skin_types';
    const SKIN_CONCERNS = 'skin_concerns';
    const PRODUCT_TYPES = 'product_types';
    const INGREDIENTS = 'ingredients';
    const BRANDS = 'brands';
    const ATTR_SET = 'Default';
    const ATTR_GROUP = 'Skin Care';
    const ATTR_ARR = [
        self::SKIN_TYPES    => 'Skin Types',
        self::SKIN_CONCERNS => 'Skin Concerns',
        self::PRODUCT_TYPES => 'Product Types',
        self::INGREDIENTS   => 'Ingredients',
        self::BRANDS        => 'Brands'
    ];

    const CATEGORY_ARR = [
        'Skin Types',
        'Skin Concerns',
        'Product Types',
        'Ingredients',
        'Brands'
    ];

    /**
     * @param string $categoryName
     * @return array
     * @throws LocalizedException
     */
    public function getSubCategories($categoryName): array;

    /**
     * @return DataObject[]
     * @throws LocalizedException
     */
    public function getAllCategories(): array;
}