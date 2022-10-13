<?php

namespace IdeaInYou\BigCommerce\Service;

use Exception;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use IdeaInYou\BigCommerce\Helper\Logger;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Rest\Request;

class SyncStock
{
    const ENDPOINT = "catalog/products";
    const PRODUCT_GET_ENDPOINT = 'offers';
    const BASE_URL = 'https://feelunique-prod.mirakl.net/api/offers';
    const ACCESS_TOKEN = '72ab8764-b05c-49d4-9468-7e266c8e9d0d';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ClientFactory
     */
    private $clientFactory;
    /**
     * @var ResponseFactory
     */
    private $responseFactory;
    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirerInterface;
    /**
     * @var \IdeaInYou\BigCommerce\Service\BigCommerceApiService
     */
    private $bigCommerceApiService;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    private Logger $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param CountryInformationAcquirerInterface $countryInformationAcquirerInterface
     * @param BigCommerceApiService $bigCommerceApiService
     * @param SerializerInterface $serializer
     * @param Logger $logger
     */
    public function __construct(
        ScopeConfigInterface                $scopeConfig,
        ClientFactory                       $clientFactory,
        ResponseFactory                     $responseFactory,
        CountryInformationAcquirerInterface $countryInformationAcquirerInterface,
        BigCommerceApiService               $bigCommerceApiService,
        SerializerInterface                 $serializer,
        Logger                              $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->countryInformationAcquirerInterface = $countryInformationAcquirerInterface;
        $this->bigCommerceApiService = $bigCommerceApiService;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function resolve()
    {
        $bigCommerceProductsArray = $this->getAllProductsFromBigCommerce();
        $miracleProductsArray = $this->getAllProductFromMiracl();
        foreach ($miracleProductsArray as $miracleProducts) {
            foreach ($miracleProducts as $miracleProduct) {
                foreach ($bigCommerceProductsArray as $bigCommerceProducts) {
                    foreach ($bigCommerceProducts as $bigCommerceProduct) {
                        if ($miracleProduct['shop_sku'] == $bigCommerceProduct['sku']) {
                            if ($miracleProduct['quantity'] != $bigCommerceProduct['inventory_level']) {
                                $miracleProduct['quantity'] = $bigCommerceProduct['inventory_level'];
                                unset($miracleProduct['logistic_class']);
                                try {
                                    $this->doRequestInMiracle(
                                        self::BASE_URL,
                                        self::PRODUCT_GET_ENDPOINT,
                                        'POST',
                                        null,
                                        $miracleProduct
                                    );
                                } catch (Exception $exception){
                                    $this->logger->error(__('Зroduct sync failed'),
                                    [
                                        "message" => $exception->getMessage(),
                                        'miracleProductSku' => $miracleProduct['shop_sku'],
                                        'bigCommerceProductSku' => $bigCommerceProduct['sku']
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getAllProductFromMiracl()
    {
        $productsArray = [];
        $response = $this->doRequestInMiracle(self::BASE_URL, self::PRODUCT_GET_ENDPOINT, 'GET');
        $array = $this->serializer->unserialize($response->getBody()->getContents());
        $productsArray[] = $array['offers'];
        $totalCount = $array['total_count'];

        for ($i = 10; $i <= $totalCount; $i += 10) {
            $offset = $i;
            $response = $this->doRequestInMiracle(self::BASE_URL, self::PRODUCT_GET_ENDPOINT, 'GET', $offset);
            $array = $this->serializer->unserialize($response->getBody()->getContents());
            $productsArray[] = $array['offers'];
        }
        return $productsArray;
    }

    /**
     * @param string $baseUrl
     * @param string $uriEndpoint
     * @param string $requestMethod
     * @param $offset
     * @param $body
     * @return Response|\Psr\Http\Message\ResponseInterface
     */
    public function doRequestInMiracle(
        string $baseUrl,
        string $uriEndpoint,
        string $requestMethod = Request::HTTP_METHOD_GET,
        $offset = null,
        $body = null
    ) {
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => $baseUrl
        ]]);

        $params = [];
        if ($body != null) {
            $body = ["offers" => [$body]];
            $jsonBody = json_encode($body);
            $params['body'] = $jsonBody;
        }
        $params['headers']['Authorization'] = self::ACCESS_TOKEN;
        $params['headers']['Content-Type'] = 'application/json';
        $params['headers']['Accept'] = 'application/json';
        if ($offset != null) {
            $params['query']['offset'] = $offset;
        }

        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            $this->logger->error('Exception :: ' . $exception->getMessage());
        }

        return $response;
    }

    /**
     * @return array
     */
    public function getAllProductsFromBigCommerce()
    {
        $productsBigCommerce = [];
        try {
            $response = $this->bigCommerceApiService->doRequest(self::ENDPOINT);
        } catch (Exception $e) {
            $this->logger->error('Exception :: ' . $e->getMessage());
        }
        $decoded_json = json_decode($response->getBody()->getContents(), true);
        $productsBigCommerce[] = $decoded_json['data'];
        if ($decoded_json['meta']['pagination']['total'] > 50) {
            $totalCount = $decoded_json['meta']['pagination']['total_pages'];
            for ($i = 2; $i <= $totalCount; $i++) {
                try {
                    $response = $this->bigCommerceApiService->doRequest(
                        self::ENDPOINT,
                        [],
                        "GET",
                        ["page" => $i]
                    );
                } catch (Exception $e) {
                    $this->logger->error('Exception :: ' . $e->getMessage());
                }
                $decoded_json = json_decode($response->getBody()->getContents(), true);
                $productsBigCommerce[] = $decoded_json['data'];
            }
        }
        return $productsBigCommerce;
    }
}
