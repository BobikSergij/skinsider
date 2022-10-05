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


namespace Mirasvit\Feed\Service\Feed;


use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManager;
use Mirasvit\Feed\Model\Feed;

class StatisticsService
{
    const CLICKS            = 'clicks';
    const ORDERS            = 'orders';
    const REVENUE           = 'revenue';
    const REVENUE_PER_CLICK = 'revenue_per_click';
    const CHART_DATA        = 'chart_data';
    const MAX_CLICKS        = 'max_clicks';

    private $priceCurrency;

    private $storeManager;

    private $store;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        StoreManager $storeManager
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->storeManager  = $storeManager;
    }

    public function getStatisticsData(Feed $feed, int $days = 14): DataObject
    {
        $statistics = $this->retrieveStatsFromDB($feed, $days);

        $currentDate = date('Y-m-d');
        $startDate   = new \Zend_Date();

        $startDate->subDay($days);

        $chartData = [];
        $clicks    = 0;
        $orders    = 0;
        $revenue   = 0;
        $maxClicks = 0;

        while ($startDate->toString('YYYY-MM-dd') !== $currentDate) {
            $date = $startDate->toString('YYYY-MM-dd');

            $chartData[$date] = 0;

            if (!isset($statistics[$date])) {
                $startDate->addDay(1);
                continue;
            }

            $dayClicks = isset($statistics[$date][self::CLICKS]) ? $statistics[$date][self::CLICKS] : 0;

            $clicks  += $dayClicks;
            $orders  += isset($statistics[$date][self::ORDERS]) ? $statistics[$date][self::ORDERS] : 0;
            $revenue += isset($statistics[$date][self::REVENUE]) ? $statistics[$date][self::REVENUE] : 0;

            $maxClicks = max($maxClicks, $dayClicks);

            $chartData[$date] = $dayClicks;

            $startDate->addDay(1);
        }

        $stats = new DataObject();

        $stats->setData(self::CLICKS, $clicks)
            ->setData(self::ORDERS, $orders)
            ->setData(self::REVENUE, $revenue)
            ->setData(self::REVENUE_PER_CLICK, $clicks ? $revenue / $clicks : 0)
            ->setData(self::CHART_DATA, $chartData)
            ->setData(self::MAX_CLICKS, $maxClicks);

        return $stats;
    }

    public function retrieveStatsFromDB(Feed $feed, int $days = 14): array
    {
        $resource   = $feed->getResource();
        $connection = $resource->getConnection();

        $startDate = new \Zend_Date();
        $startDate->subDay($days);

        $endDate = new \Zend_Date();

        $query = 'SELECT SUM(IFNULL(is_click, 0)) AS clicks, COUNT(DISTINCT order_id) AS orders,'
            . ' SUM(IFNULL(subtotal, 0)) AS revenue, DATE_FORMAT(created_at, "%Y-%m-%d") AS date'
            . ' FROM ' . $resource->getTable('mst_feed_report')
            . ' WHERE feed_id = ' . $feed->getId()
            . ' AND created_at >= "' . $startDate->toString('YYYY-MM-dd 00:00:00') . '"'
            . ' AND created_at <= "' . $endDate->toString('YYYY-MM-dd 23:59:59') . '"'
            . ' GROUP BY day(created_at) ORDER BY created_at';

        $res = $connection->query($query)->fetchAll();

        $preparedData = [];

        foreach ($res as $value) {
            if (isset($value['date'])) {
                $preparedData[$value['date']] = $value;
            }
        }

        return $preparedData;
    }

    public function formatCurrency($value): string
    {
        if (!$this->store) {
            $this->store = $this->storeManager->getDefaultStoreView();
        }

        return $this->priceCurrency->convertAndFormat(
            $value,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->store,
            $this->store->getDefaultCurrencyCode()
        );
    }
}
