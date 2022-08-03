<?php

namespace IdeaInYou\CategorySortingOptions\Plugin;

use Magento\Catalog\Model\Config;

class AddSortingOptionsPlugin
{
    /**
     * @param Config $subject
     * @param array $result
     * @return array
     */
    public function afterGetAttributeUsedForSortByArray(Config $subject, array $result): array
    {
        unset($result['position']);
        unset($result['name']);
        unset($result['price']);
        $result['position'] = __('Featured Items');
        $result['created_at'] = __('Newest Items');
        $result['best_selling'] = __('Best Selling');
        $result['name'] = __('A to Z');
        $result['name_revert'] = __('Z to A');
        $result['by_review'] = __('By Review');
        $result['price'] = __('Price Ascending');
        $result['price_revert'] = __('Price Descending');
        return $result;
    }
}
