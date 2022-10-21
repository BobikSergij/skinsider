<?php

namespace IdeaInYou\BigCommerce\Plugin;

use Exception;
use Magento\Sales\Model\Order;
use Mirakl\MMP\Shop\Domain\Order\ShopOrder;
use MiraklSeller\Api\Model\Connection;
use MiraklSeller\Sales\Helper\Order\Import;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class CheckProductAvailability
{
    private ProductRepositoryInterface $productRepository;
    private ProductInterfaceFactory $productFactory;
    private StockRegistryInterface $stockRegistry;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ProductInterfaceFactory $productFactory
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductInterfaceFactory $productFactory,
        StockRegistryInterface $stockRegistry
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param Import $subject
     * @param ShopOrder $miraklOrder
     * @param Connection $connection
     * @return void
     */
    public function beforeCreateOrder(Import $subject, ShopOrder $miraklOrder, Connection $connection)
    {
        $items = $miraklOrder->getOrderLines()->getItems();
        foreach ($items as $item){
            $data = $item->getData('offer')->getData();
            $availability = $this->availabilityCheck($data['sku']);
            if($availability == false){
                $this->createProduct($data);
            }
        }
    }

    /**
     * @param $data
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function createProduct($data){
        $product = $this->productFactory->create();
        $product->setName($data['product']->getData('title'));
        $product->setSku($data['sku']);
        $product->setPrice($data['price']);
        $product->setVisibility(1);
        $product->setAttributeSetId(4);
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $this->productRepository->save($product);
        $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
        $stockItem->setQty(5);
        $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
    }

    /**
     * @param $sku
     * @return bool
     */
    public function availabilityCheck($sku)
    {
        try{
            $this->productRepository->get($sku);
            return true;
        } catch (Exception $exception){
            return false;
        }
    }
}
