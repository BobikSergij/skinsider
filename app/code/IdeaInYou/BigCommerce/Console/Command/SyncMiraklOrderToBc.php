<?php

declare(strict_types=1);

namespace IdeaInYou\BigCommerce\Console\Command;

use Exception;
use IdeaInYou\BigCommerce\Service\Mirakl\OrderSync as MiraklOrderSyncService;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncMiraklOrderToBc extends Command
{
    /**
     * @var MiraklOrderSyncService
     */
    private $miraklOrderSyncService;

    /**
     * @param MiraklOrderSyncService $miraklOrderSyncService
     */
    public function __construct(
        MiraklOrderSyncService $miraklOrderSyncService
    ) {
        $this->miraklOrderSyncService = $miraklOrderSyncService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('bc:sync:miraklorders');
        $this->setDescription('This is my first console command.');
        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Synchronization of Mirakl Orders</info>');

        try {
            $this->miraklOrderSyncService->syncOrders();
        } catch (LocalizedException $e) {
            $output->writeln($e->getMessage());
            $exitCode = 1;
        }

        return $exitCode;
    }
}
