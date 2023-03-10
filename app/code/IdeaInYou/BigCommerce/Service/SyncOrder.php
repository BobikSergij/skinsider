<?php

namespace IdeaInYou\BigCommerce\Service;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use IdeaInYou\BigCommerce\Helper\Logger;
use IdeaInYou\BigCommerce\Service\Mirakl\Order as MiraklOrderApiService;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SyncOrder
{
    const SYNC_TIMEFRAME = 45; // in minutes
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
    protected $scopeConfig;
    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param OrderApiService $orderApiService
     * @param MiraklOrderApiService $miraklOrderApiService
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     */
    public function __construct(
        OrderApiService $orderApiService,
        MiraklOrderApiService $miraklOrderApiService,
        ScopeConfigInterface $scopeConfig,
        Logger $logger
    ) {
        $this->orderApiService = $orderApiService;
        $this->miraklOrderApiService = $miraklOrderApiService;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
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

            if ($miraklOrder->getId() == $miraklOrderId && strtolower($miraklOrder->getStatus()->getState()) !== "shipped") {
                try {
                    $bcOrderShipment = $this->getBigCommerceFirstShipmentData($bcOrder->id);
                    $this->updateMiraklOrderTrackingInfo($miraklOrderId, $bcOrderShipment);
                    $this->miraklOrderApiService->shipOrder($miraklOrder->getId());
                    $this->logger->info(
                        __("Miracle order status and tracking info changed."),
                        [
                            "bigCommerceOrderId" => $bcOrder->id,
                            "miracleOrderId" => $miraklOrder->getId()
                        ]
                    );
                } catch (Exception $e) {
                    $this->logger->error(__("ERROR update miracle order status and tracking info"), [
                        'message' => $e->getMessage(),
                        "bigCommerceOrderId" => $bcOrder->id,
                        "miracleOrderId" => $miraklOrder->getId()
                    ]);
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
            $this->logger->error(__('Error for get all orders'),
                [
                    "message" => $e->getMessage()
                ]);
        }

        return $result ?? [];
    }

    /**
     * @param $bcOrderId
     * @return mixed|null
     */
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
            $this->logger->error('Exception :: ' . $e->getMessage());
        }

        return $result ?? [];
    }

    /**
     * @param $miraklOrderId
     * @param $bcOrderShipment
     * @return bool|null
     * @throws Exception
     */
    public function updateMiraklOrderTrackingInfo($miraklOrderId, $bcOrderShipment)
    {
        if (!$bcOrderShipment || !$bcOrderShipment->id) {
            return null;

            try {
                $this->miraklOrderApiService->updateOrderTrackingInfo(
                    $miraklOrderId,
                    'YODEL',
                    BigCommerceApiService::YODEL_SHIPMENT_METHOD_BC,
                    $bcOrderShipment->tracking_number,
                    $bcOrderShipment->tracking_link
                );
                $this->logger->info(
                    __("Miracle order tracking updated"),
                    [
                    "bigCommerceOrderId" => $bcOrderShipment->id,
                    "miraklOrderId" => $miraklOrderId
                ]
                );
            } catch (Exception $exception) {
                $this->logger->error(
                    __("ERROR update miracle order tracking"),
                    [
                    "message" => $exception->getMessage(),
                    "bigCommerceOrderId" => $bcOrderShipment->id,
                    "miraklOrderId" => $miraklOrderId
                ]
                );
            }

            return true;
        }
    }
}
