<?php

namespace IdeaInYou\BigCommerce\Service;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use IdeaInYou\BigCommerce\Service\Mirakl\Order as MiraklOrderApiService;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SyncOrder
{
    const SYNC_TIMEFRAME = 150; // in minutes
    /**
     * @var OrderApiService
     */
    protected $orderApiService;
    /**
     * @var MiraklOrderApiService
     */
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
     * @throws Exception
     */
    public function resolve()
    {
        $this->logic();
    }

    /**
     * @return void
     * @throws Exception
     */
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

            if ($bcOrder->id == 368708 || $miraklOrder->getId() == $miraklOrderId && strtolower($miraklOrder->getStatus()->getState()) !== "shipped") {
                try {
                    $bcOrderShipment = $this->getBigCommerceFirstShipmentData($bcOrder->id);
                    $this->updateMiraklOrderTrackingInfo($miraklOrderId, $bcOrderShipment);
                    $this->miraklOrderApiService->shipOrder($miraklOrder->getId());
                } catch (Exception $e) {
                    // track error
                }
            }
        }
    }

    /**
     * @param $bcOrders
     * @return array
     */
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

    /**
     * @param $miraklOrderIds
     * @return array
     * @throws Exception
     */
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

    /**
     * @return array
     */
    public function getBigCommerceOrders(): array
    {
        $currentTime = date('c');
        $second = (strtotime($currentTime) - (60 * self::SYNC_TIMEFRAME));
        $lastTime = date('c', $second);

        $qparams['query'] = [
            "min_date_modified" => $lastTime,
            "max_date_modified" => $currentTime,
        ];

        try {
            $response = $this->orderApiService->getAllOrder($qparams);
            $result = json_decode($response->getBody()->getContents());
        } catch (GuzzleException $e) {
        }

        return $result ?? [];
    }

    public function getBigCommerceFirstShipmentData($bcOrderId)
    {
        $shipments = $this->getBigCommerceOrderShipments($bcOrderId);
        return count($shipments) ? $shipments[0] : null;
    }
    /**
     * @param $bcOrderId
     * @return array
     */
    public function getBigCommerceOrderShipments($bcOrderId): array
    {
        try {
            $response = $this->orderApiService->getOrderShipment($bcOrderId);
            $result = json_decode($response->getBody()->getContents());
        } catch (GuzzleException $e) {
        }

        return $result ?? [];
    }

    public function updateMiraklOrderTrackingInfo($miraklOrderId, $bcOrderShipment)
    {
        if (!$bcOrderShipment || !$bcOrderShipment->id)
            return null;

        $this->miraklOrderApiService->updateOrderTrackingInfo(
            $miraklOrderId,
            'yodel',
            BigCommerceApiService::YODEL_SHIPMENT_METHOD_BC,
            $bcOrderShipment->tracking_number,
            $bcOrderShipment->tracking_link
        );

        return true;
    }
}
