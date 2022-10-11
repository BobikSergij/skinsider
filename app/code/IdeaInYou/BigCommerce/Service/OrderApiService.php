<?php

declare(strict_types=1);

namespace IdeaInYou\BigCommerce\Service;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class OrderApiService extends AbstractApiService
{
    const API_SCOPE = "/orders";
    const API_VERSION = "2";
    const BC_ORDER_STATUSES = [
        "shipped" => 2,
        "awaiting_fulfillment" => 11
    ];

    /**
     * @return string
     */
    protected function getScope(): string
    {
        return self::API_SCOPE;
    }

    /**
     * @return string
     */
    protected function getApiVersion(): string
    {
        return self::API_VERSION;
    }

    /**
     * https://developer.bigcommerce.com/api-reference/a91f7bd42e987-get-an-order
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function getOrder($id, $params = [])
    {
        $result = $this->get("/$id", $params);
        return $result;
    }

    /**
     * https://developer.bigcommerce.com/api-reference/82f91b58d0c98-get-all-orders
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function getAllOrder($params = [])
    {
        $result = $this->get('', $params);
        return $result;
    }

    /**
     * https://developer.bigcommerce.com/api-reference/69382bdc67723-get-a-count-of-orders
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function getOrderCount($params = [])
    {
        $result = $this->get('/count', $params);
        return $result;
    }

    /**
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function getOrderShipment($orderId, $params = [])
    {
        // {{baseUrl}}/{{store_hash}}/v2/orders/:order_id/shipments

        $result = $this->get("/$orderId/shipments",$params);
        return $result;
    }
}
