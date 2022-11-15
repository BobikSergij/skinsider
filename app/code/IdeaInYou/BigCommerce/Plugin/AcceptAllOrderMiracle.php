<?php

namespace IdeaInYou\BigCommerce\Plugin;


use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Mirakl\MMP\Shop\Domain\Order\ShopOrder;
use MiraklSeller\Api\Helper\Order as ApiOrder;
use MiraklSeller\Api\Model\Connection;
use MiraklSeller\Api\Model\ConnectionFactory;
use MiraklSeller\Api\Model\ResourceModel\ConnectionFactory as ConnectionResourceFactory;
use MiraklSeller\Core\Helper\Config;
use MiraklSeller\Process\Model\Process as ProcessModel;
use MiraklSeller\Sales\Helper\Order\Price as PriceHelper;
use MiraklSeller\Sales\Model\MiraklOrder\Acceptance\Backorder;
use MiraklSeller\Sales\Model\MiraklOrder\Acceptance\InsufficientStock;
use MiraklSeller\Sales\Model\MiraklOrder\Acceptance\PricesVariations;

class AcceptAllOrderMiracle extends AbstractHelper
{

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var ApiOrder
     */
    protected $apiOrder;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ConnectionFactory
     */
    protected $connectionFactory;

    /**
     * @var ConnectionResourceFactory
     */
    protected $connectionResourceFactory;

    /**
     * @var Backorder
     */
    protected $backorderHandler;

    /**
     * @var InsufficientStock
     */
    protected $insufficientStockHandler;

    /**
     * @var PricesVariations
     */
    protected $pricesVariationsHandler;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var bool
     */
    protected $isMsiEnabled;

    /**
     * @param   Context                     $context
     * @param   ProductRepositoryInterface  $productRepository
     * @param   StockRegistryInterface      $stockRegistry
     * @param   ApiOrder                    $apiOrder
     * @param   Config                      $config
     * @param   ConnectionFactory           $connectionFactory
     * @param   ConnectionResourceFactory   $connectionResourceFactory
     * @param   Backorder                   $backorderHandler
     * @param   InsufficientStock           $insufficientStockHandler
     * @param   PricesVariations            $pricesVariationsHandler
     * @param   PriceHelper                 $priceHelper
     * @param   ObjectManagerInterface      $objectManager
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        ApiOrder $apiOrder,
        Config $config,
        ConnectionFactory $connectionFactory,
        ConnectionResourceFactory $connectionResourceFactory,
        Backorder $backorderHandler,
        InsufficientStock $insufficientStockHandler,
        PricesVariations $pricesVariationsHandler,
        PriceHelper $priceHelper,
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);

        $this->productRepository         = $productRepository;
        $this->stockRegistry             = $stockRegistry;
        $this->apiOrder                  = $apiOrder;
        $this->config                    = $config;
        $this->connectionFactory         = $connectionFactory;
        $this->connectionResourceFactory = $connectionResourceFactory;
        $this->backorderHandler          = $backorderHandler;
        $this->insufficientStockHandler  = $insufficientStockHandler;
        $this->pricesVariationsHandler   = $pricesVariationsHandler;
        $this->priceHelper               = $priceHelper;
        $this->objectManager             = $objectManager;
        $this->isMsiEnabled              = $objectManager->get(\MiraklSeller\Core\Helper\Data::class)->isMsiEnabled();
    }

    /**
     * @param   ProcessModel    $process
     * @param   int             $connectionId
     * @return  ProcessModel
     */
    public function acceptConnectionOrders(ProcessModel $process, $connectionId)
    {
        $connection = $this->getConnectionById($connectionId);

        if (!$connection->getId()) {
            return $process->fail(__("Could not find connection with id '%1'", $connectionId));
        }

        $process->output(__("Accepting Mirakl orders of connection '%1' (id: %2) ...", $connection->getName(), $connection->getId()));

        $params = ['order_states' => [\Mirakl\MMP\Common\Domain\Order\OrderState::WAITING_ACCEPTANCE]];
        $miraklOrders = $this->apiOrder->getAllOrders($connection, $params);

        if (!$miraklOrders->count()) {
            return $process->output(__('No Mirakl order to accept for this connection'));
        }

        /** @var ShopOrder $miraklOrder */
        foreach ($miraklOrders as $miraklOrder) {
            try {
                $process->output(__('Processing Mirakl order #%1 ...', $miraklOrder->getId()));
                $this->acceptMiraklOrder($process, $connection, $miraklOrder);
            } catch (\Exception $e) {
                $process->output(__('ERROR: %1', $e->getMessage()));
            }
        }

        return $process;
    }

    /**
     * @param   ProcessModel    $process
     * @param   Connection      $connection
     * @param   ShopOrder       $miraklOrder
     * @return  ProcessModel
     */
    public function acceptMiraklOrder(ProcessModel $process, Connection $connection, ShopOrder $miraklOrder)
    {
        // Build order lines to accept
        $orderLines = [];

        $stockId = 1;
        if ($this->isMsiEnabled) {
            $stockByWebsiteId = $this->objectManager->get('Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface');
            $stockId = $stockByWebsiteId->execute($connection->getWebsiteId())->getStockId();
        }

        /** @var \Mirakl\MMP\Common\Domain\Order\ShopOrderLine $orderLine */
        foreach ($miraklOrder->getOrderLines() as $orderLine) {
            $accepted = true; // Order line is accepted by default


            $orderLines[] = [
                'id'       => $orderLine->getId(),
                'accepted' => $accepted,
            ];
        }

        $this->apiOrder->acceptOrder($connection, $miraklOrder->getId(), $orderLines);

        $process->output(__('Order has been accepted successfully.'));

        return $process;
    }

    /**
     * Retrieves Mirakl connection by specified id
     *
     * @param   int $connectionId
     * @return  Connection
     */
    protected function getConnectionById($connectionId)
    {
        $connection = $this->connectionFactory->create();
        $this->connectionResourceFactory->create()->load($connection, $connectionId);

        return $connection;
    }

}
