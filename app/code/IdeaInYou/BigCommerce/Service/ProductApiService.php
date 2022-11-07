<?php

declare(strict_types=1);

namespace IdeaInYou\BigCommerce\Service;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class ProductApiService extends AbstractApiService
{
    const API_SCOPE = "/catalog/products";
    const API_SCOPE_VARIANTS = "/catalog/variants";
    const API_VERSION = "3";

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
     * https://developer.bigcommerce.com/api-reference/82f91b58d0c98-get-all-orders
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function getAllProducts($params = [], $scope = '')
    {
        $result = $this->get('', $params, $scope);
        return $result;
    }
}
