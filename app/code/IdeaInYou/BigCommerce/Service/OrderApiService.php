<?php

declare(strict_types=1);

namespace IdeaInYou\BigCommerce\Service;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class OrderApiService extends AbstractApiService
{
    const ORDER_API_SCOPE = "/orders";
    const API_VERSION = "2";
    const BC_ORDER_STATUSES = [
        "shipped" => 2
    ];

    protected function getScope(): string
    {
        return self::ORDER_API_SCOPE;
    }

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
    public function getOrder($id)
    {
        $result = $this->get("/$id");
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
    public function getOrderCount()
    {
        $result = $this->get('/count');
        return $result;
    }
}
