<?php

namespace IdeaInYou\BigCommerce\Service;

use GuzzleHttp\Exception\GuzzleException;
use IdeaInYou\BigCommerce\Service\Mirakl\Order as MiraklOrderApiService;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SyncOrder
{
    const SYNC_TIMEFRAME = 10; // in minutes

    protected $orderApiService;
    protected $miraklOrderApiService;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param OrderApiService $orderApiService
     * @param MiraklOrderApiService $miraklOrderApiService
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        OrderApiService $orderApiService,
        MiraklOrderApiService $miraklOrderApiService,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->orderApiService = $orderApiService;
        $this->miraklOrderApiService = $miraklOrderApiService;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return void
     */
    public function resolve()
    {
//        $bigCommerceProductsArray = $this->getBigCommerceOrders();
        $this->logic();
    }

    public function logic()
    {
        $bcOrders = $this->getBigCommerceOrders();
        if (!count($bcOrders)) {
            return;
        }

        $bcOrderOnMirakl = $this->normalizeBcOrderData($bcOrders);
        $miraklOrderIds = array_keys($bcOrderOnMirakl);
        $miraklOrders = $this->getMiraklOrders($miraklOrderIds);

        foreach ($bcOrders as $bcOrder) {
            $miraklOrderId = $bcOrder->external_id;
            if (empty($miraklOrderId) || $bcOrder->status_id != OrderApiService::BC_ORDER_STATUSES["shipped"]) {
                continue;
            }
            $miraklOrder = $miraklOrders[$miraklOrderId];

            if ($miraklOrder->getId() == $miraklOrderId && strtolower($miraklOrder->getStatus()->getState()) !== "shipped") {
                try {
                    $this->miraklOrderApiService->shipOrder($miraklOrder->getId());
                } catch (\Exception $e) {
                    // track error
                }
            }
        }
    }

    public function normalizeBcOrderData($bcOrders = [])
    {
        $miraklOrderIds = [];

        foreach ($bcOrders as $bcOrder) {
            if (isset($bcOrder->external_id)) {
                $miraklOrderIds[$bcOrder->external_id] = $bcOrder;
            }
        }

        //extract external_id field
        return $miraklOrderIds;
    }

    public function getMiraklOrders($miraklOrderIds)
    {
        $params = [
            "order_ids" => implode(',', $miraklOrderIds) // ToDo fix format
        ];
        $miraklOrders = $this->miraklOrderApiService->getAllOrders($params);
        $result = [];
        foreach ($miraklOrders as $miraklOrder) {
            $result["{$miraklOrder->getId()}"] = $miraklOrder;
        }
        return $result;
    }

    public function getBigCommerceOrders(): array
    {
        date_default_timezone_set("Europe/Kyiv");

        $currentTime = date('c');
        $second = (strtotime($currentTime) - (60 * self::SYNC_TIMEFRAME));
        $lastTime = date('c', $second);

        $qparams['query'] = [
            "min_date_modified" => $lastTime,
            "max_date_modified" => $currentTime,
        ];
        $accessToken = $this->scopeConfig->getValue('bigCommerce/api_group/bigCommerce_access_token');

        try {
            $qparams['headers']['X-Auth-Token'] = $accessToken;
            $qparams['headers']['Content-Type'] = 'application/json';
            $qparams['headers']['Accept'] = 'application/json';
            $response = $this->orderApiService->getAllOrder($qparams);
            $result = json_decode($response->getBody()->getContents());
        } catch (GuzzleException $e) {
        }

        return $result ?? [];
    }
}
