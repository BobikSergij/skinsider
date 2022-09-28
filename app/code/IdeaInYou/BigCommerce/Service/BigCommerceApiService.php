<?php

declare(strict_types=1);

namespace IdeaInYou\BigCommerce\Service;

use _PHPStan_c862bb974\Nette\Utils\Json;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use phpDocumentor\Reflection\Types\Void_;
use Psr\Http\Message\ResponseInterface;

class BigCommerceApiService
{
    const API_PATH_VARIABLE = "bigCommerce/api_group/bigCommerce_api_path";
    const ACCESS_TOKEN_VARIABLE = 'bigCommerce/api_group/bigCommerce_access_token';
    const ORDER_CREATION_ENDPOINT = 'orders';

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
     * @param ScopeConfigInterface $scopeConfig
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param CountryInformationAcquirerInterface $countryInformationAcquirerInterface
     * @param OrderRepositoryInterface $orderRepositoryInterface
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        CountryInformationAcquirerInterface $countryInformationAcquirerInterface,
        OrderRepositoryInterface $orderRepositoryInterface
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->countryInformationAcquirerInterface = $countryInformationAcquirerInterface;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
    }

    /**
     * @param Order $order
     * @return Response|ResponseInterface|void
     */
    public function createOrder(Order $order)
    {
        if ( !$order->getData('big_commerce_id')) {

            $payload['status_id'] = $this->getBigCommerceOrderStatusId($order->getStatus());
            $payload = $this->collectBillingData($order, $payload);
            $payload['default_currency_code'] = $order->getOrderCurrencyCode();
            $payload['discount_amount'] = $order->getDiscountAmount() ?? 0;
            $payload = $this->collectShippingData($order, $payload);
            $payload = $this->collectProductsData($order, $payload);

            $response = $this->doRequest(self::ORDER_CREATION_ENDPOINT, $payload,'POST');
            $decoded_json = json_decode($response->getBody()->getContents(), true);
            $bigCommerceId = $decoded_json['id'];

            $orderInterface = $this->orderRepositoryInterface->get($order->getId());
            $orderInterface->setData('big_commerce_id', $bigCommerceId);
            $this->orderRepositoryInterface->save($orderInterface);
            return $response;
        }
    }

    /**
     * @param Order $order
     * @return Response|ResponseInterface
     */
    public function updateOrder( Order $order ) {
        $bigCommerceId = $order->getData('big_commerce_id');

        if ( $bigCommerceId ) {

            $payload['status_id'] = $this->getBigCommerceOrderStatusId($order->getStatus());
            $payload = $this->collectBillingData($order, $payload);
            $payload = $this->collectShippingData($order, $payload);
            $payload = $this->collectProductsData($order, $payload);

            $response = $this->doRequest(self::ORDER_CREATION_ENDPOINT . '/' . $bigCommerceId, $payload,'PUT');
            return $response;
        }
    }

    /**
     * @param Order $order
     * @param array $payload
     * @return array
     */
    public function collectBillingData(Order $order, array $payload = []){
        $payload['billing_address']['first_name'] = $order->getBillingAddress()->getFirstname();
        $payload['billing_address']['last_name'] = $order->getBillingAddress()->getLastname();
        $payload['billing_address']['street_1'] = implode( ", ", $order->getBillingAddress()->getStreet());
        $payload['billing_address']['city'] = $order->getBillingAddress()->getCity();
        $payload['billing_address']['state'] = $order->getBillingAddress()->getRegion();
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
    public function collectShippingData(Order $order, array $payload = []){
        $addresses = $order->getAddressesCollection();
        $addressesItems = $addresses->getItems();
        foreach ( $addressesItems as $addressesItem ) {
            if ( $addressesItem->getAddressType() == 'shipping') {
                $key = key($addressesItem);
                $payload['shipping_addresses'][$key]['first_name'] = $addressesItem->getFirstname();
                $payload['shipping_addresses'][$key]['last_name'] = $addressesItem->getLastname();
                $payload['shipping_addresses'][$key]['street_1'] = implode( ", ", $addressesItem->getStreet());
                $payload['shipping_addresses'][$key]['city'] = $addressesItem->getCity();
                $payload['shipping_addresses'][$key]['state'] = $addressesItem->getRegion() ?? '';
                $payload['shipping_addresses'][$key]['zip'] = $addressesItem->getPostcode();
                $payload['shipping_addresses'][$key]['country'] = $this->getCountryName($addressesItem->getCountryId());
                $payload['shipping_addresses'][$key]['country_iso2'] = $addressesItem->getCountryId();
                $payload['shipping_addresses'][$key]['phone'] = $addressesItem->getTelephone();
                $payload['shipping_addresses'][$key]['email'] = $addressesItem->getEmail();
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
    public function collectProductsData(Order $order, array $payload = []){
        $items = $order->getItems();

        foreach ( $items as $item ) {
            $key = key($items);
            $payload['products'][$key]['name'] = $items[$key]->getName();
            $payload['products'][$key]['quantity'] = $items[$key]->getQtyOrdered();
            $payload['products'][$key]['price_inc_tax'] = $items[$key]->getPriceInclTax();
            $payload['products'][$key]['price_ex_tax'] = $items[$key]->getPrice();
        }
        return $payload;
    }

    /**
     * Getting Country Name
     * @param string $countryCode
     * @param string $type
     *
     * @return null|string
     * */
    protected function getCountryName(string $countryCode, string $type="local"){
        $countryName = null;
        try {
            $data = $this->countryInformationAcquirerInterface->getCountryInfo($countryCode);
            $countryName = $data->getFullNameLocale();
        } catch (NoSuchEntityException $e) {}
        return $countryName;
    }

    /**
     * @param $status
     * @return int
     */
    protected function getBigCommerceOrderStatusId($status){
        $result = 1;
        switch ($status){
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
     * @return ResponseInterface|Response
     */
    protected function doRequest(string $uriEndpoint, array $payload = [], string $requestMethod = Request::HTTP_METHOD_GET)
    {
        $config = $this->scopeConfig;
        $baseUrl = $config->getValue('bigCommerce/api_group/bigCommerce_api_path');
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

        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }
}
