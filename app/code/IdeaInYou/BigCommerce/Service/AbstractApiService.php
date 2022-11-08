<?php

declare(strict_types=1);

namespace IdeaInYou\BigCommerce\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Webapi\Rest\Request;
use IdeaInYou\BigCommerce\Helper\Logger;

abstract class AbstractApiService
{
    const BASE_URL_CONFIG_PATH = "bigCommerce/api_group/bigCommerce_api_path";
    const API_ACCESS_TOKEN_PATH = "bigCommerce/api_group/bigCommerce_access_token";

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ClientFactory
     */
    private $clientFactory;
    private Logger $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ClientFactory $clientFactory
     * @param Logger $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ClientFactory        $clientFactory,
        Logger               $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
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
     * @throws Exception
     */
    private function getDefaultHeaders(): array
    {
        $token = $this->scopeConfig->getValue(self::API_ACCESS_TOKEN_PATH);
        if (empty($token)) {
            throw new Exception(__("BigCommerce store token is not provided!"));
        }

        return [
                'X-Auth-Token' => $token, //'rgvd5oaesgb407npycmcwfavxhy24ux',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ];
    }

    /**
     * @param string $method
     * @param $uri
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    private function request(string $method, $uri = '', array $options = [], $scope): \Psr\Http\Message\ResponseInterface
    {
        // https://api.bigcommerce.com/stores/{token}/{version}/{scope}
        if(empty($scope)){
            $scope = $this->getScope();
        }
        $baseUrl = $this->getBaseUrl($this->getApiVersion()) . $scope;
        $uri = $baseUrl . $uri;

        $options["headers"] = isset($options["headers"]) && count($options["header"])
                                ? array_merge($options["header"], $this->getDefaultHeaders())
                                : $this->getDefaultHeaders();
        $this->logger->info(__("Request."),
            [
                "uri" => $uri,
                "options" => $options
            ]);

        return $this->getClient()->request(
            $method,
            $uri,
            $options
        );
    }

    /**
     * @throws GuzzleException
     */
    protected function get($uri = '', array $options = [], $scope = ''): \Psr\Http\Message\ResponseInterface
    {
        return $this->request(Request::HTTP_METHOD_GET, $uri, $options, $scope);
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
