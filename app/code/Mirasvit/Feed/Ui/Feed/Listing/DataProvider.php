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


namespace Mirasvit\Feed\Ui\Feed\Listing;


use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManager;
use Mirasvit\Feed\Block\Adminhtml\Feed\Renderer\Link;
use Mirasvit\Feed\Block\Adminhtml\Feed\Renderer\Status;
use Mirasvit\Feed\Block\Adminhtml\Feed\Listing\Statistics;
use Mirasvit\Feed\Block\Adminhtml\Feed\Listing\StatisticsFactory;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    private $feedStatusRenderer;

    private $feedLinkRenderer;

    private $priceCurrency;

    private $storeManager;

    private $statisticksBlockFactory;

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function __construct(
        Status $feedStatusRenderer,
        Link $feedLinkRenderer,
        PriceCurrencyInterface $priceCurrency,
        StoreManager $storeManager,
        StatisticsFactory $statisticksBlockFactory,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->feedStatusRenderer = $feedStatusRenderer;
        $this->feedLinkRenderer   = $feedLinkRenderer;
        $this->priceCurrency      = $priceCurrency;
        $this->storeManager       = $storeManager;

        $this->statisticksBlockFactory = $statisticksBlockFactory;

        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems          = [];
        $arrItems['items'] = [];

        $store = $this->storeManager->getDefaultStoreView();

        /** @var \Mirasvit\Feed\Model\Feed $item */
        foreach ($searchResult->getItems() as $item) {
            $itemData = $item->getData();
            $itemData['feed_status']    = $this->feedStatusRenderer->render($item);
            $itemData['file']           = $this->feedLinkRenderer->render($item);
            $itemData['generated_time'] = gmdate('H:i:s', (int)$itemData['generated_time']);

            $itemData['revenue_per_click'] = !$itemData['clicks']
                ? 0
                : $this->priceCurrency->convertAndFormat(
                    $itemData['revenue'] / $itemData['clicks'],
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    $store,
                    $store->getDefaultCurrencyCode()
                );

            $itemData['revenue'] = $this->priceCurrency->convertAndFormat(
                $itemData['revenue'],
                false,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $store,
                $store->getDefaultCurrencyCode()
            );

            $itemData['statistics'] = $this->prepareStatisticksOutput($item);

            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }

    public function prepareStatisticksOutput(\Mirasvit\Feed\Model\Feed $item, int $days = 14): string
    {
        $statisticksBlock = $this->statisticksBlockFactory->create();
        $statisticksBlock->setData('feed', $item)
            ->setData('interval', $days);

        return $statisticksBlock->toHtml();
    }
}
