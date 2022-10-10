<?php

declare(strict_types=1);

namespace IdeaInYou\BigCommerce\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Webapi\Rest\Request;

abstract class AbstractApiService
{
    const BASE_URL_CONFIG_PATH = "bigCommerce/api_group/bigCommerce_api_path";

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ClientFactory        $clientFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->clientFactory = $clientFactory;
    }

    /**
     * @param $v -> Means 2, 3
     * @return string
     *
     *
     * https://api.bigcommerce.com/stores/95gfrbk7ak/{version}/{scope}/{method}
     */
    protected function getBaseUrl(string $v = ''): string
    {
        if (!empty($v) && $v[0] !== '/') {
            $v = "v$v";
        }
        $baseUrl = $this->scopeConfig->getValue(self::BASE_URL_CONFIG_PATH) . $v;
        return $baseUrl;
    }

    abstract protected function getScope(): string;
    abstract protected function getApiVersion(): string;

    /**
     * @return Client
     */
    private function getClient()
    {
        return $this->clientFactory->create(['config' => [
            'base_uri' => $this->getBaseUrl()
        ]]);
    }

    /**
     * @throws GuzzleException
     */
    private function request(string $method, $uri = '', array $options = []): \Psr\Http\Message\ResponseInterface
    {
        // https://api.bigcommerce.com/stores/{token}/{version}/{scope}
        $baseUrl = $this->getBaseUrl($this->getApiVersion()) . $this->getScope();
        $uri = $baseUrl . $uri;
        return $this->getClient()->request(
            $method,
            $uri,
            $options
        );
    }

    /**
     * @throws GuzzleException
     */
    protected function get($uri = 'orders', array $options = []): \Psr\Http\Message\ResponseInterface
    {
        return $this->request(Request::HTTP_METHOD_GET, $uri, $options);
    }

    /**
     * @throws GuzzleException
     */
    protected function post($uri = '', array $options = []): \Psr\Http\Message\ResponseInterface
    {
        return $this->request(Request::HTTP_METHOD_POST, $uri, $options);
    }

    /**
     * @throws GuzzleException
     */
    protected function put($uri = '', array $options = []): \Psr\Http\Message\ResponseInterface
    {
        return $this->request(Request::HTTP_METHOD_PUT, $uri, $options);
    }

    /**
     * @throws GuzzleException
     */
    protected function delete($uri = '', array $options = []): \Psr\Http\Message\ResponseInterface
    {
        return $this->request(Request::HTTP_METHOD_DELETE, $uri, $options);
    }
}
