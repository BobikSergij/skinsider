<?php

namespace IdeaInYou\CategorySortingOptions\Plugin;

use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellersCollectionFactory;
use Magento\Store\Model\StoreManager;
use Zend_Db_Select;

class SetCollectionPlugin
{
    const BESTSELLERS_SORT_BY = 'best_selling';
    const ALPHABET_REVERT_SORT_BY = 'name_revert';
    const REVIEW_SORT_BY = 'by_review';
    const PRICE_REVERT_SORT_BY = 'price_revert';

    /**
     * Products collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $collection = null;

    /**
     * @var BestSellersCollectionFactory
     */
    private $bestSellersCollectionFactory;
    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var Resource
     */
    protected $resource;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param BestSellersCollectionFactory $bestSellersCollectionFactory
     * @param StoreManager $storeManager
     * @param ResourceConnection $resource
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        BestSellersCollectionFactory $bestSellersCollectionFactory,
        StoreManager $storeManager,
        ResourceConnection $resource,
        CollectionFactory $collectionFactory
    ) {
        $this->bestSellersCollectionFactory = $bestSellersCollectionFactory;
        $this->storeManager = $storeManager;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param Toolbar $subject
     * @param callable $proceed
     * @param $collection
     * @return Toolbar
     */
    public function aroundSetCollection(Toolbar $subject, callable $proceed, $collection)
    {
        $currentOrder = $subject->getCurrentOrder();
        $currentDirection = $subject->getCurrentDirection();
        $result = $proceed($collection);

        if ($currentOrder) {
            $collection = $subject->getCollection();
            $defaultPageSize = $collection->getPageSize();
            $currentPage = $collection->getCurPage();
            switch ($currentOrder) {
                case self::BESTSELLERS_SORT_BY:
                    $collection->setPageSize($collection->getSize());
                    $collection->getSelect()
                        ->reset(Zend_Db_Select::ORDER)
                        ->joinLeft(
                            'sales_order_item',
                            'e.entity_id = sales_order_item.product_id',
                            ['qty_ordered'=>'SUM(sales_order_item.qty_ordered)']
                        )
                        ->group('e.entity_id');

                    $this->sortJoinedCollection($collection, 'qty_ordered', $currentPage, $defaultPageSize);
                    break;
                case self::ALPHABET_REVERT_SORT_BY:
                    $collection->addAttributeToSort(
                        'name',
                        'desc'
                    );
                    break;
                case self::REVIEW_SORT_BY:
                    $collection->setPageSize($collection->getSize());
                    $collection->getSelect()->joinLeft(
                        'review_entity_summary',
                        'e.entity_id = review_entity_summary.entity_pk_value',
                        ['rating_summary'=>'SUM(review_entity_summary.rating_summary)']
                    )
                        ->group('e.entity_id');

                    $this->sortJoinedCollection($collection, 'reviews_count', $currentPage, $defaultPageSize);
                    break;
                case self::PRICE_REVERT_SORT_BY:
                    $collection->addAttributeToSort(
                        'price',
                        'desc'
                    );
                    break;
                default:
                    $collection->addAttributeToSort(
                        $currentOrder,
                        $currentDirection
                    );
                    break;
            }

            return $subject;
        }

        return $result;
    }

    /**
     * @param $collection
     * @param $field
     * @param $currentPage
     * @param $defaultPageSize
     * @return void
     */
    private function sortJoinedCollection($collection, $field, $currentPage, $defaultPageSize)
    {
        $products = [];
        foreach ($collection as $item) {
            $products[] = $item;
        }

        $productsCount = count($products);

        for ($i = 0; $i < $productsCount; $i++) {
            for ($j = $i+1; $j < $productsCount; $j++) {
                $tmp = $products[$j];
                if ($products[$j]->getData($field) > $products[$i]->getData($field)) {
                    $products[$j] = $products[$i];
                    $products[$i] = $tmp;
                }
            }
        }
        $curPageProducts = [];
        for ($i = $currentPage*$defaultPageSize-$defaultPageSize; $i < $currentPage*$defaultPageSize; $i++) {
            if (array_key_exists($i, $products)) {
                $curPageProducts[] = $products[$i];
            }
        }

        $collection->removeAllItems();
        foreach ($curPageProducts as $product) {
            $collection->addItem($product);
        }

        $collection->setPageSize($defaultPageSize);
    }
}
