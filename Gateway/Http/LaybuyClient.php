<?php

namespace Laybuy\Laybuy\Gateway\Http;

use Laybuy\Laybuy\Model\Config;
use Laybuy\Laybuy\Model\Logger\Logger;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class LaybuyClient
 * @package Laybuy\Laybuy\Gateway\Http
 */
class LaybuyClient
{
    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var integer|string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * LaybuyClient constructor.
     *
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(
        Logger $logger,
        Config $config
    ) {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     *
     * @param $laybuyOrder
     * @param $storeId
     * @return bool
     */
    public function getRedirectUrlAndToken($laybuyOrder, $storeId)
    {
        $restClient = $this->setupLaybuyClient($this->config, $storeId);
        $response = $restClient->restPost(Config::API_ORDER_CREATE, json_encode($laybuyOrder));
        $body = json_decode($response->getBody());
        $returnData = [];
        $this->logger->debug([__METHOD__ => $body]);

        if ($body->result == Config::LAYBUY_SUCCESS) {
            if (!$body->paymentUrl || !$body->token) {
                return false;
            }
            $returnData['redirectUrl'] = $body->paymentUrl;
            $returnData['token'] = $body->token;

            return $returnData;
        }

        return false;
    }

    /**
     * @param $token
     * @param $storeId
     * @return bool
     */
    public function getLaybuyConfirmationOrderId($token, $storeId)
    {
        $restClient = $this->setupLaybuyClient($this->config, $storeId);
        $response = $restClient->restPost(Config::API_ORDER_CONFIRM, json_encode(['token' => $token]));
        $body = json_decode($response->getBody());

        $this->logger->debug(['method' => __METHOD__, $token => $body]);

        if ($body->result == Config::LAYBUY_SUCCESS) {
            if (!$body->orderId) {
                return false;
            }

            return $body->orderId;
        }

        return false;
    }

    /**
     * @param $token
     * @param $storeId
     * @return bool
     */
    public function cancelLaybuyOrder($token, $storeId)
    {
        $restClient = $this->setupLaybuyClient($this->config, $storeId);

        $response = $restClient->restGet(Config::API_ORDER_CANCEL . '/' . $token);
        $body = json_decode($response->getBody());

        if ($body->result == Config::LAYBUY_SUCCESS) {
            return true;
        }

        return false;
    }

    /**
     * @param Config $config
     * @param integer $storeId
     * @return \Zend_Rest_Client|null
     */
    protected function setupLaybuyClient(Config $config, $storeId = null)
    {
        if (!$config->getMerchantId($storeId) || !$config->getApiKey($storeId)) {
            return null;
        }

        if ($config->getUseSandbox($storeId)) {
            $this->endpoint = $config::API_ENDPOINT_SANDBOX;
        } else {
            $this->endpoint = $config::API_ENDPOINT_LIVE;
        }

        $this->merchantId = $config->getMerchantId($storeId);
        $this->apiKey = $config->getApiKey($storeId);

        try {
            $restClient = new RestClient($this->endpoint);
            $restClient->getHttpClient()->setAuth($this->merchantId, $this->apiKey,
                \Zend_Http_Client::AUTH_BASIC);
            return $restClient;
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * @param array $refundDetails
     * @param $storeId
     * @return int
     * @throws LocalizedException
     */
    public function refundLaybuyOrder($refundDetails, $storeId)
    {
        $restClient = $this->setupLaybuyClient($this->config, $storeId);
        $response = $restClient->restPost(Config::API_ORDER_REFUND, json_encode($refundDetails));

        $body = json_decode($response->getBody());

        $this->logger->debug([
            'Refund Response:' => $body,
            'Store ID:' => $storeId
        ]);

        if ($body->result === Config::LAYBUY_FAILURE) {
            $this->logger->debug(['Error while processing refund: ' . $response->getBody()]);
            throw new LocalizedException(__('Unable to process refund.'));
        }

        return $body->refundId;
    }

     /**
     * @param $token
     * @param $storeId
     * @return array
     */
    public function checkMerchantOrder($merchantReference, $storeId)
    {
        $restClient = $this->setupLaybuyClient($this->config, $storeId);
        $response = $this->restClient->restGet(Config::API_ORDER_CHECK . '/' . $merchantReference);
        $body = json_decode($response->getBody());

        $this->logger->debug(['method' => __METHOD__, 'Response' => $body]);

        if ($body->result == Config::LAYBUY_SUCCESS) {
            return $body;
        }

        return false;
    }
}
