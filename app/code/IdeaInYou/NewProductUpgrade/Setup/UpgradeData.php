<?php

namespace IdeaInYou\NewProductUpgrade\Setup;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param State $appState
     * @param ProductRepository $productRepository
     * @param DirectoryList $directoryList
     */
    public function __construct(
        State $appState,
        ProductRepository $productRepository,
        DirectoryList $directoryList
    ) {
        $this->state = $appState;
        $this->productRepository = $productRepository;
        $this->directoryList = $directoryList;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $ex) {
            $this->state->setAreaCode('adminhtml');
        }

        $baseUrl = $this->directoryList->getRoot();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->read($baseUrl . '/app/code/IdeaInYou/NewProductUpgrade/productsMissingTabsInfoAdditional.csv');
        }
    }

    /**
     * @param $filePath
     * @return void
     */
    public function read($filePath)
    {
        $file = fopen($filePath, 'r', '"');
        $header = fgetcsv($file);
        while ($row = fgetcsv($file, 3000, ",")) {
            $data_count = count($row);
            if ($data_count < 1) {
                continue;
            }
            $data = array_combine($header, $row);
            $this->addProductInfo($data);
        }
    }

    /**
     * @param $data
     * @return void
     */
    public function addProductInfo($data)
    {
        $sku = $data['Product SKU'];
        $warranty = $data['Warranty'];
        $productRepository = $this->productRepository;

        try {
            $product = $productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            $product = false;
        }

        if ($product && $sku != 'BOJ0013' && !$product->getData('how_to_use_text')) {
            if ($product->getWarranty() && $product->getPrice()) {
                $breakWord = '<div id="ingredients-text">';
                $parts = explode($breakWord, $warranty);
                if (array_key_exists(0, $parts)) {
                    $howToUse = $parts[0];
                    $product->setData('how_to_use_text', $howToUse);
                }

                if (array_key_exists(1, $parts)) {
                    $ingredients = $breakWord . $parts[1];
                    $product->setData('ingredients_text', $ingredients);
                }
                try {
                    $productRepository->save($product);
                } catch (CouldNotSaveException|InputException|StateException $e) {
                }
            }
        }
    }
}
