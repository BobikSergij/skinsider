<?php

declare(strict_types=1);

namespace IdeaInYou\BigCommerce\Service;

use Exception;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use IdeaInYou\BigCommerce\Helper\Logger;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class BigCommerceApiService
{
    const API_PATH_VARIABLE = "bigCommerce/api_group/bigCommerce_api_path";
    const ACCESS_TOKEN_VARIABLE = 'bigCommerce/api_group/bigCommerce_access_token';
    const ORDER_CREATION_ENDPOINT = 'orders';
    const YODEL_SHIPMENT_METHOD_BC = 'Yodel (1-2 working days)';
    const DEFAULT_PAYMENT_METHOD = 'Feelunique';
    const DEFAULT_PAYMENT_TYPE = 'paid';

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
     * @var OrderRepositoryInterface
     */
    private $orderRepositoryInterface;

    /**
     * @var ProductApiService
     */
    private $productApiService;
    private Logger $logger;
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param CountryInformationAcquirerInterface $countryInformationAcquirerInterface
     * @param OrderRepositoryInterface $orderRepositoryInterface
     * @param ProductApiService $productApiService
     * @param Logger $logger
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        ScopeConfigInterface                $scopeConfig,
        ClientFactory                       $clientFactory,
        ResponseFactory                     $responseFactory,
        CountryInformationAcquirerInterface $countryInformationAcquirerInterface,
        OrderRepositoryInterface            $orderRepositoryInterface,
        ProductApiService                   $productApiService,
        Logger                              $logger,
        AttributeRepositoryInterface        $attributeRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->countryInformationAcquirerInterface = $countryInformationAcquirerInterface;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->productApiService = $productApiService;
        $this->logger = $logger;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param Order $order
     * @return Response|ResponseInterface|void
     */
    public function createOrder(Order $order)
    {
        if (!$order->getData('big_commerce_id')) {
            try {
                $payload = [];
                $payload['status_id'] = $this->getBigCommerceOrderStatusId($order->getStatus());
                $payload = $this->collectBillingData($order, $payload);
                $payload['default_currency_code'] = $order->getOrderCurrencyCode();
                $payload['discount_amount'] = $order->getDiscountAmount() ?? 0;
                $payload = $this->collectShippingData($order, $payload);
                $payload = $this->collectProductsData($order, $payload);
                $payload["external_id"] = $order->getMiraklOrderId();
                $payload["payment_method"] = self::DEFAULT_PAYMENT_METHOD;
                $payload["status_id"] = OrderApiService::BC_ORDER_STATUSES["awaiting_fulfillment"];

                $response = $this->doRequest(self::ORDER_CREATION_ENDPOINT, $payload, 'POST');
                $decoded_json = json_decode($response->getBody()->getContents(), true);
                $bigCommerceId = $decoded_json['id'];

                $bcProductIds = [];
                foreach ($payload["products"] as $bcProductData) {
                    $bcProductIds[] = $bcProductData["product_id"];
                }

                $this->logger->info(
                    __("BigCommerce order created."),
                    [
                        "bigCommerceId" => $bigCommerceId,
                        "miracleOrderId" => $order->getMiraklOrderId(),
                        "bigCommerceProductIds" => implode(",", $bcProductIds),
                    ]
                );

                $orderInterface = $this->orderRepositoryInterface->get($order->getId());
                $orderInterface->setData('big_commerce_id', $bigCommerceId);
                $this->orderRepositoryInterface->save($orderInterface);
                return $response;
            } catch (Exception $exception) {
                $this->logger->error(
                    __("BigCommerce order creation process was failed."),
                    [
                        "error_message" => $exception->getMessage(),
                        "order_id" => $order->getId(),
                        "request_payload" => $payload
                    ]
                );
            }
        }
    }

    /**
     * @param Order $order
     */
    public function updateOrder(Order $order)
    {
        $bigCommerceId = $order->getData('big_commerce_id');

        if ($bigCommerceId) {
            try {
                $payload['status_id'] = $this->getBigCommerceOrderStatusId($order->getStatus());
                $payload = $this->collectBillingData($order, $payload);
                $payload = $this->collectShippingData($order, $payload);
                $payload = $this->collectProductsData($order, $payload);

                $response = $this->doRequest(self::ORDER_CREATION_ENDPOINT . '/' . $bigCommerceId, $payload, 'PUT');
            } catch (Exception $exception) {
                $this->logger->error('Exception :: ' . $exception->getMessage());
            }
        }
    }

    /**
     * @param Order $order
     * @param array $payload
     * @return array
     */
    public function collectBillingData(Order $order, array $payload = [])
    {
        $payload['billing_address']['first_name'] = $order->getBillingAddress()->getFirstname();
        $payload['billing_address']['last_name'] = $order->getBillingAddress()->getLastname();
        $payload['billing_address']['street_1'] = implode(", ", $order->getBillingAddress()->getStreet());
        $payload['billing_address']['city'] = $order->getBillingAddress()->getCity();
        if($order->getBillingAddress()->getRegion() !== null) {
            $payload['billing_address']['state'] = $order->getBillingAddress()->getRegion();
        }else {
            $payload['billing_address']['state'] = $order->getBillingAddress()->getCity();
        }
        $payload['billing_address']['zip'] = $order->getBillingAddress()->getPostcode();
        $payload['billing_address']['country'] = $this->getCountryName($order->getBillingAddress()->getCountryId());
        $payload['billing_address']['country_iso2'] = $order->getBillingAddress()->getCountryId();
        $payload['billing_address']['phone'] = $order->getBillingAddress()->getTelephone();
        $payload['billing_address']['email'] = $order->getCustomerEmail();
        return $payload;
    }

    /**
     * @param Order $order
     * @param array $payload
     * @return array
     */
    public function collectShippingData(Order $order, array $payload = [])
    {
        $addresses = $order->getAddressesCollection();
        $addressesItems = $addresses->getItems();
        foreach ($addressesItems as $key => $addressesItem) {
            if ($addressesItem->getAddressType() == 'shipping') {
                $payload['shipping_addresses'][$key]['first_name'] = $addressesItem->getFirstname();
                $payload['shipping_addresses'][$key]['last_name'] = $addressesItem->getLastname();
                $payload['shipping_addresses'][$key]['street_1'] = implode(", ", $addressesItem->getStreet());
                $payload['shipping_addresses'][$key]['city'] = $addressesItem->getCity();
                if($addressesItem->getRegion() !== null) {
                    $payload['shipping_addresses'][$key]['state'] = $addressesItem->getRegion() ?? '';
                }else {
                    $payload['shipping_addresses'][$key]['state'] = $addressesItem->getCity();
                }
                $payload['shipping_addresses'][$key]['zip'] = $addressesItem->getPostcode();
                $payload['shipping_addresses'][$key]['country'] = $this->getCountryName($addressesItem->getCountryId());
                $payload['shipping_addresses'][$key]['country_iso2'] = $addressesItem->getCountryId();
                $payload['shipping_addresses'][$key]['phone'] = $addressesItem->getTelephone();
                $payload['shipping_addresses'][$key]['email'] = $addressesItem->getEmail();
                if ($order->getShippingethod() !== self::YODEL_SHIPMENT_METHOD_BC) {
                    $order->setShippingMethod(self::YODEL_SHIPMENT_METHOD_BC);
                }
                $payload['shipping_addresses'][$key]['shipping_method'] = $order->getShippingMethod();
            }
            $payload['shipping_cost_ex_tax'] = $order->getShippingAmount() ?? 0;
            $payload['shipping_cost_inc_tax'] = $order->getShippingInclTax() ?? 0;
        }
        return $payload;
    }

    /**
     * @param Order $order
     * @param array $payload
     * @return array
     */
    public function collectProductsData(Order $order, array $payload = [])
    {
        $items = $order->getItems();

        foreach ($items as $key => $item) {
            $payload['products'][$key]['name'] = $item->getName();
            $payload['products'][$key]['quantity'] = $item->getQtyOrdered();
            $payload['products'][$key]['sku'] = $item->getSku();
            $payload['products'][$key]['price_inc_tax'] = $item->getPriceInclTax();
            $payload['products'][$key]['price_ex_tax'] = $item->getPrice();

            if ($this->getBcProduct($item->getSku()) && $this->getBcProduct($item->getSku())->id  ) {
                $payload['products'][$key]['product_id'] = $this->getBcProduct($item->getSku())->id;
                if($this->variantId($item->getSku()) !== false) {
                    $payload['products'][$key]['variant_id'] = $this->variantId($item->getSku());
                }
            }
        }
        return $payload;
    }

    /**
     * Getting Country Name
     * @param string $countryCode
     * @param string $type
     *
     * @return null|string
     */
    protected function getCountryName(string $countryCode, string $type = "local")
    {
        $countryName = null;
        try {
            $data = $this->countryInformationAcquirerInterface->getCountryInfo($countryCode);
            $countryName = $data->getFullNameLocale();
        } catch (NoSuchEntityException $e) {
            $this->logger->error('Exception :: ' . $e->getMessage());
        }
        return $countryName;
    }

    /**
     * @param $status
     * @return int
     */
    protected function getBigCommerceOrderStatusId($status)
    {
        $result = 1;
        switch ($status) {
            case 'complete':
                $result = 10;
                break;
            case 'cancelled':
                $result = 5;
                break;
        }
        return $result;
    }

    /**
     * @param string $uriEndpoint
     * @param array $payload
     * @param string $requestMethod
     * @param array $queryParams
     * @return Response|ResponseInterface
     */
    public function doRequest(
        string $uriEndpoint,
        array  $payload = [],
        string $requestMethod = Request::HTTP_METHOD_GET,
               $queryParams = []
    ) {
        $config = $this->scopeConfig;
        if (str_contains($uriEndpoint, 'orders')) {
            $baseUrl = $config->getValue('bigCommerce/api_group/bigCommerce_api_path') . 'v2';
        } else {
            $baseUrl = $config->getValue('bigCommerce/api_group/bigCommerce_api_path') . 'v3';
        }
        $accessToken = $config->getValue('bigCommerce/api_group/bigCommerce_access_token');
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => $baseUrl
        ]]);

        $jsonBody = json_encode($payload);
        $params = [];
        $params['body'] = $jsonBody;

        $params['headers']['X-Auth-Token'] = $accessToken;
        $params['headers']['Content-Type'] = 'application/json';
        $params['headers']['Accept'] = 'application/json';
        //$params['query']['page'] = $queryParams["page"];
        $params['query'] = $queryParams;
        $uriEndpoint = $baseUrl . "/" . $uriEndpoint;
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
     * @param $sku
     * @return mixed|null
     */
    public function getBcProduct($sku)
    {
        $params = [
            "query" => [
                "sku" => $sku
            ]
        ];

        try {
            $productsStr = $this->getBaseProduct($params);
            if($productsStr == false){
                $productsStr = $this->getVariantsProduct($params);
            }
            $products = $productsStr;

            if (!isset($products->data) || !count($products->data)) {
                throw new Exception(__("Get All Product request data is empty"));
            }
            $product = $products->data[0];
            $this->logger->info(
                "Searching for BigCommerce product by SKU",
                [
                    "sku" => $sku,
                    "big_commerce_product_id" => $bcProduct->id ?? 0
                ]
            );
            return $product;
        } catch (Exception|Throwable $e) {
            $this->logger->error(
                "Product was not found in BigCommerce on create order.",
                [
                    "sku" => $sku,
                    "error_message" => $e->getMessage()
                ]
            );
        }

        return null;
    }

    /**
     * @param $params
     * @return bool
     * @throws GuzzleException
     */
    public function getBaseProduct($params){
            $response = json_decode($this->productApiService->getAllProducts($params)->getBody()->getContents());
            if(!empty($response->data)){
                return $response;
            }else{
                return false;
            }
    }

    /**
     * @param $params
     * @return false|string
     * @throws GuzzleException
     */
    public function getVariantsProduct($params){
        try{
            $request = $this->productApiService->getAllProducts($params, "/catalog/variants")->getBody()->getContents();
            $request = json_decode($request);
            $params['query']['id'] = $request->data[0]->product_id;
            unset($params['query']['sku']);
            $this->logger->info(
                "Searching for BigCommerce variants product by id",
                [
                    "product_id" => $request->data[0]->product_id
                ]
            );
            $product = json_decode($this->productApiService->getAllProducts($params)->getBody()->getContents());
            return $product;
        } catch (Exception $exception){
            return false;
        }
    }

    /**
     * @param $sku
     * @return false
     * @throws GuzzleException
     */
    public function variantId($sku){
        $params = [
            "query" => [
                "sku" => $sku
            ]
        ];
        try {
            $request = $this->productApiService->getAllProducts($params, "/catalog/variants")->getBody()->getContents();
            $request = json_decode($request);
            return $request->data[0]->id;
        } catch (Exception $exception){
            return false;
        }
    }
}
