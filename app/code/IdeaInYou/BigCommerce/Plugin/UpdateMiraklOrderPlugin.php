<?php

namespace IdeaInYou\BigCommerce\Plugin;

use IdeaInYou\BigCommerce\Service\BigCommerceApiService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Mirakl\MMP\Shop\Domain\Order\ShopOrder;
use MiraklSeller\Sales\Model\Synchronize\Shipments;

class UpdateMiraklOrderPlugin
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
     * @param Shipments $subject
     * @param bool $result
     * @param Order $magentoOrder
     * @param ShopOrder $miraklOrder
     * @return bool
     */
    public function afterSynchronize(Shipments $subject, bool $result, Order $magentoOrder, ShopOrder $miraklOrder): bool
    {
        if ( $result && $this->scopeConfig->getValue('bigCommerce/api_group/enabled') ){
            $this->apiService->updateOrder($magentoOrder);
        }
        return $result;
    }
}
