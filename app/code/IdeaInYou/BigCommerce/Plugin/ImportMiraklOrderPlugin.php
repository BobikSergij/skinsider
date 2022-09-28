<?php

namespace IdeaInYou\BigCommerce\Plugin;

use IdeaInYou\BigCommerce\Service\BigCommerceApiService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Mirakl\MMP\Shop\Domain\Order\ShopOrder;
use MiraklSeller\Api\Model\Connection;
use MiraklSeller\Sales\Helper\Order\Import;

class ImportMiraklOrderPlugin
{
    /**
     * @var BigCommerceApiService
     */
    private $apiService;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param BigCommerceApiService $apiService
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        BigCommerceApiService $apiService,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->apiService = $apiService;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Import $subject
     * @param Order $result
     * @param ShopOrder $miraklOrder
     * @param Connection $connection
     * @return Order
     */
    public function afterCreateOrder(Import $subject, Order $result, ShopOrder $miraklOrder, Connection $connection): Order
    {
        if ( $this->scopeConfig->getValue('bigCommerce/api_group/enabled')){
            $this->apiService->createOrder($result);
        }
        return $result;
    }
}
