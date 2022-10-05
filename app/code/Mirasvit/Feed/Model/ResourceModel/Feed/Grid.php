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


namespace Mirasvit\Feed\Model\ResourceModel\Feed;


use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

class Grid extends SearchResult
{
    protected $document = \Mirasvit\Feed\Model\Feed::class;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = "mst_feed_feed",
        $resourceModel = \Mirasvit\Feed\Model\ResourceModel\Feed::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        $this->_select->joinLeft(
            ['report' => $this->getTable('mst_feed_report')],
            'report.feed_id = main_table.feed_id',
            [
                'clicks'  => 'SUM(IFNULL(report.is_click, 0))',
                'orders'  => 'COUNT(DISTINCT report.order_id)',
                'revenue' => 'SUM(IFNULL(report.subtotal, 0))'
            ]
        );

        $this->_select->group('main_table.feed_id');

        return parent::_initSelect();
    }
}
