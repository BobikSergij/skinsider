<?php

namespace IdeaInYou\BigCommerce\Service\Mirakl;

use Exception;
use MiraklSeller\Api\Helper\Order as MiraklApiOrderHelper;
use MiraklSeller\Api\Model\ResourceModel\Connection\CollectionFactory as ConnectionCollectionFactory;

class Order
{
    /**
     * @var MiraklApiOrderHelper
     */
    protected $apiOrderHelper;
    /**
     * @var ConnectionCollectionFactory
     */
    protected $connectionCollectionFactory;
    /**
     * @var \MiraklSeller\Api\Model\Connection
     */
    protected $connection;

    /**
     * @param MiraklApiOrderHelper $apiOrderHelper
     * @param ConnectionCollectionFactory $connectionCollectionFactory
     */
    public function __construct(
        MiraklApiOrderHelper $apiOrderHelper,
        ConnectionCollectionFactory $connectionCollectionFactory
    ) {
        $this->apiOrderHelper = $apiOrderHelper;
        $this->connectionCollectionFactory = $connectionCollectionFactory;
        $this->connection = $this->connectionCollectionFactory->create()->getFirstItem();
    }

    // in case if there is only ONE CONNECTION registered!!!

    /**
     * @param $params
     * @return \Mirakl\MMP\Shop\Domain\Collection\Order\ShopOrderCollection
     * @throws Exception
     */
    public function getAllOrders($params)
    {
        if (!$this->connection->getId()) {
            throw new Exception(__("No Mirakl connection registered!"));
        }

        return $this->apiOrderHelper->getAllOrders($this->connection, $params);
    }

    /**
     * @param $orderId
     * @return bool
     * @throws Exception
     */
    public function shipOrder($orderId)
    {
        if (!$this->connection->getId()) {
            throw new Exception(__("No Mirakl connection registered!"));
        }

        $this->apiOrderHelper->shipOrder($this->connection, $orderId);
        return true;
    }

    /**
     * @param $orderId
     * @param $code
     * @param $name
     * @param $number
     * @param $link
     * @return bool
     * @throws Exception
     */
    public function updateOrderTrackingInfo($orderId, $code = '', $name = '', $number = '', $link = '')
    {
        if (!$this->connection->getId()) {
            throw new Exception(__("No Mirakl connection registered!"));
        }

        $this->apiOrderHelper->updateOrderTrackingInfo($this->connection, $orderId, $code, $name, $number, $link);
        return true;
    }
}
