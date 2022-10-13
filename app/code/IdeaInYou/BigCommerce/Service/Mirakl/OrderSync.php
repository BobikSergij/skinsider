<?php

namespace IdeaInYou\BigCommerce\Service\Mirakl;

use Exception;
use MiraklSeller\Api\Model\ResourceModel\Connection\CollectionFactory as ConnectionCollectionFactory;
use MiraklSeller\Process\Model\Process;
use MiraklSeller\Process\Model\ProcessFactory;
use \MiraklSeller\Sales\Helper\Order\Sync as MiraklOrderSync;
use \MiraklSeller\Sales\Helper\Order\Process as MiraklOrderSyncProcess;
use IdeaInYou\BigCommerce\Helper\Logger;

class OrderSync
{
    /**
     * @var ConnectionCollectionFactory
     */
    protected $connectionCollectionFactory;
    /**
     * @var \MiraklSeller\Api\Model\Connection
     */
    protected $connection;
    /**
     * @var MiraklOrderSync
     */
    protected $miraklOrderSync;
    /**
     * @var MiraklOrderSyncProcess
     */
    protected $miraklOrderProcessSync;
    /**
     * @var ProcessFactory
     */
    protected $processFactory;
    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param ConnectionCollectionFactory $connectionCollectionFactory
     * @param MiraklOrderSync $miraklOrderSync
     * @param MiraklOrderSyncProcess $miraklOrderProcessSync
     * @param ProcessFactory $processFactory
     * @param Logger $logger
     */
    public function __construct(
        ConnectionCollectionFactory $connectionCollectionFactory,
        MiraklOrderSync $miraklOrderSync,
        MiraklOrderSyncProcess $miraklOrderProcessSync,
        ProcessFactory $processFactory,
        Logger $logger
    ) {
        $this->connectionCollectionFactory = $connectionCollectionFactory;
        $this->processFactory = $processFactory;
        $this->miraklOrderSync = $miraklOrderSync;
        $this->miraklOrderProcessSync = $miraklOrderProcessSync;
        $this->connection = $this->connectionCollectionFactory->create()->getFirstItem();
        $this->logger = $logger;
    }

    /**
     * @return Process
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function synchronizeConnection()
    {
        if (!$this->connection->getId()) {
            throw new Exception(__("No Mirakl connection registered!"));
        }

        $processType = Process::TYPE_ADMIN;
        $processStatus = Process::STATUS_PENDING;

        return $this->miraklOrderSync->synchronizeConnection($this->connection, $processType, $processStatus);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function synchronizeAllConnections()
    {
        $processes = $this->miraklOrderSync->synchronizeAllConnections(Process::TYPE_CRON, Process::STATUS_IDLE);

        /** @var Process $process */
        foreach ($processes as $process) {
            $process->run(true);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function synchronizeOrders()
    {
        try {
            if (!$this->connection->getId()) {
                throw new Exception(__("No Mirakl connection registered!"));
            }
            /** @var TYPE_NAME $processType */
            /** @var TYPE_NAME $processStatus */
            $process = $this->processFactory->create()
                ->setType($processType)
                ->setStatus($processStatus)
                ->setName('Synchronize Mirakl orders')
                ->setHelper(\MiraklSeller\Sales\Helper\Order\Process::class)
                ->setMethod('synchronizeConnection')
                ->setParams([$this->connection->getId()]);

            $processes = $this->miraklOrderProcessSync->synchronizeConnection(Process::TYPE_CRON, Process::STATUS_IDLE);
        } catch (Exception $exception) {
            $this->logger->error('Exception :: '. $exception->getMessage());
        }
        /** @var Process $process */
        foreach ($processes as $process) {
            $process->run(true);
        }
    }
}
