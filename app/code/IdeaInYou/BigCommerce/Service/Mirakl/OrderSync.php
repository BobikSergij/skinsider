<?php

namespace IdeaInYou\BigCommerce\Service\Mirakl;

use Exception;
use MiraklSeller\Api\Model\ResourceModel\Connection\CollectionFactory as ConnectionCollectionFactory;
use MiraklSeller\Process\Model\Process;
use MiraklSeller\Process\Model\ProcessFactory;
use \MiraklSeller\Sales\Helper\Order\Sync as MiraklOrderSync;
use \MiraklSeller\Sales\Helper\Order\Process as MiraklOrderSyncProcess;


class OrderSync
{
    protected $connectionCollectionFactory;
    protected $connection;
    protected $miraklOrderSync;
    protected $miraklOrderProcessSync;
    protected $processFactory;

    public function __construct(
        ConnectionCollectionFactory $connectionCollectionFactory,
        MiraklOrderSync $miraklOrderSync,
        MiraklOrderSyncProcess $miraklOrderProcessSync,
        ProcessFactory $processFactory
    ) {
        $this->connectionCollectionFactory = $connectionCollectionFactory;
        $this->processFactory = $processFactory;
        $this->miraklOrderSync = $miraklOrderSync;
        $this->miraklOrderProcessSync = $miraklOrderProcessSync;
        $this->connection = $this->connectionCollectionFactory->create()->getFirstItem();
    }

    /**
     * @throws Exception
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
     * @throws Exception
     */
    public function synchronizeOrders()
    {
        if (!$this->connection->getId()) {
            throw new Exception(__("No Mirakl connection registered!"));
        }
        $process = $this->processFactory->create()
            ->setType($processType)
            ->setStatus($processStatus)
            ->setName('Synchronize Mirakl orders')
            ->setHelper(\MiraklSeller\Sales\Helper\Order\Process::class)
            ->setMethod('synchronizeConnection')
            ->setParams([$this->connection->getId()]);

        $processes = $this->miraklOrderProcessSync->synchronizeConnection(Process::TYPE_CRON, Process::STATUS_IDLE);

        /** @var Process $process */
        foreach ($processes as $process) {
            $process->run(true);
        }
    }
}
