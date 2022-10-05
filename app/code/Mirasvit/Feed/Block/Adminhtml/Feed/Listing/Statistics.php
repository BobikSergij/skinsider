<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.2.8
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\Feed\Block\Adminhtml\Feed\Listing;


use Magento\Backend\Block\Template;
use Magento\Framework\DataObject;
use Mirasvit\Feed\Service\Feed\StatisticsService;

class Statistics extends Template
{
    protected $_template = "Mirasvit_Feed::feed/listing/statistics.phtml";

    private $statisticsService;

    /** @var DataObject|null */
    private $stats;

    public function __construct(
        StatisticsService $statisticsService,
        Template\Context $context
    ) {
        $this->statisticsService = $statisticsService;

        parent::__construct($context);
    }

    public function prepareStats(): bool
    {
        $feed = $this->getData('feed');

        if (!$feed) {
            return false;
        }

        $interval = $this->getData('interval');

        $this->stats = $this->statisticsService->getStatisticsData($feed, $interval);

        return true;
    }

    public function getChartData(): array
    {
        return $this->stats->getData(StatisticsService::CHART_DATA) ?: [];
    }

    public function getClicks(): int
    {
        return (int)$this->stats->getData(StatisticsService::CLICKS);
    }

    public function getOrdersCnt(): int
    {
        return (int)$this->stats->getData(StatisticsService::ORDERS);
    }

    public function getRevenue(): string
    {
        return $this->statisticsService->formatCurrency($this->stats->getData(StatisticsService::REVENUE));
    }

    public function getRevenuePerClick(): string
    {
        return $this->statisticsService->formatCurrency($this->stats->getData(StatisticsService::REVENUE_PER_CLICK));
    }

    public function getMaxClicks(): int
    {
        return (int)$this->stats->getData(StatisticsService::MAX_CLICKS);
    }
}
