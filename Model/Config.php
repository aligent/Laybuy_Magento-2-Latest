<?php

namespace Laybuy\Laybuy\Model;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use \Magento\Payment\Gateway\Config\Config as ParentConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Config
 * @package Laybuy\Laybuy\Model
 */
class Config extends ParentConfig
{
    const CODE = 'laybuy_payment';

    const KEY_ACTIVE = 'active';

    const USE_SANDBOX = 'sandbox';

    const KEY_MIN_ORDER_TOTAL = 'min_order_total';

    const KEY_MAX_ORDER_TOTAL = 'max_order_total';

    const PAYMENT_ACTION = 'payment_action';

    const KEY_TITLE = 'title';

    const KEY_MERCHANT_ID = 'merchant_id';

    const KEY_API_KEY = 'merchant_api_key';

    const KEY_SHOW_IN_PRODUCT_PAGE = 'show_in_product_page';

    const KEY_SHOW_IN_CATEGORY_PAGE = 'show_in_category_page';

    const KEY_SHOW_IN_CART_PAGE = 'show_in_cart_page';

    const KEY_SHOW_FULL_LOGO = 'show_full_logo';

    const API_ENDPOINT_LIVE = 'https://api.laybuy.com';

    const API_ENDPOINT_SANDBOX = 'https://sandbox-api.laybuy.com';

    const API_ORDER_CREATE = '/order/create';

    const API_ORDER_CONFIRM = '/order/confirm';

    const API_ORDER_CANCEL = '/order/cancel';

    const API_ORDER_REFUND = '/order/refund';

    const API_ORDER_CHECK = '/order/merchant';

    const LAYBUY_SUCCESS = 'SUCCESS';

    const LAYBUY_FAILURE = 'ERROR';

    const LAYBUY_CANCELLED = 'CANCELLED';

    const LAYBUY_FIELD_REFERENCE_ORDER_ID = 'Reference Order Id';

    const SUPPORTED_CURRENCY_CODES = ['NZD', 'AUD', 'GBP'];

    const FULL_LOGO = 'logo/full.svg';

    const SMALL_LOGO = 'logo/small.svg';

    const ASSET_URL = 'https://integration-assets.laybuy.com/magento1_laybuy/';

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var int
     */
    protected $currentStore;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        StoreManagerInterface $storeManager,
        $methodCode = self::CODE,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
    }

    /**
     * Get Payment configuration status
     *
     * @param integer $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)$this->getValue(self::KEY_ACTIVE, $storeId);
    }

    /**
     * Get Show In Page Configuration
     *
     * @param integer $storeId
     * @return bool
     */
    public function showInProductPage($storeId = null)
    {
        return (bool)$this->getValue(self::KEY_SHOW_IN_PRODUCT_PAGE, $storeId);
    }

    /**
     * Get Show In Category Configuration
     *
     * @param integer $storeId
     * @return bool
     */
    public function showInCategoryPage($storeId = null)
    {
        return (bool)$this->getValue(self::KEY_SHOW_IN_CATEGORY_PAGE, $storeId);
    }

    /**
     * Get Show In Cart Configuration
     *
     * @param integer $storeId
     * @return bool
     */
    public function showInCartPage($storeId = null)
    {
        return (bool)$this->getValue(self::KEY_SHOW_IN_CART_PAGE, $storeId);
    }

    /**
     * Get Payment configuration status
     *
     * @param integer $storeId
     * @return string
     */
    public function getLogo($storeId = null)
    {
        if((bool)$this->getValue(self::KEY_SHOW_FULL_LOGO, $storeId)){
            return self::FULL_LOGO;
        } else {
            return self::SMALL_LOGO;
        }

    }

    /**
     * Get Image from CDN path
     *
     * @return string
     */
    public function getMagentoAssetUrl($imagePath)
    {
        return self::ASSET_URL.$imagePath;
    }

    /**
     * Get The Laybuy Merchant ID
     *
     * @param integer $storeId
     * @return string
     */
    public function getMerchantId($storeId = null)
    {
        return $this->getValue(self::KEY_MERCHANT_ID, $storeId);
    }

    /**
     * Get teh laybuy API key (secret)
     *
     * @param integer $storeId
     * @return string
     */
    public function getApiKey($storeId = null)
    {
        $value = $this->getValue(self::KEY_API_KEY, $storeId);
        return $value ? $this->encryptor->decrypt($value) : $value;
    }


    /**
     * Gey Payment action
     *
     * @param integer $storeId
     * @return mixed
     */
    public function getPaymentAction($storeId = null)
    {
        return $this->getValue(self::PAYMENT_ACTION, $storeId);
    }

    /**
     * Get minimum order total
     *
     * @param integer $storeId
     * @return mixed
     */
    public function getMinOrderTotal($storeId = null)
    {
        return $this->getValue(self::KEY_MIN_ORDER_TOTAL, $storeId);
    }

    /**
     * Get max order total
     *
     * @param integer $storeId
     * @return mixed
     */
    public function getMaxOrderTotal($storeId = null)
    {
        return $this->getValue(self::KEY_MAX_ORDER_TOTAL, $storeId);
    }

    /**
     * Get use sanbox flag
     *
     * @param integer $storeId
     * @return string
     */
    public function getUseSandbox($storeId = null)
    {
        return $this->getValue(self::USE_SANDBOX, $storeId);
    }
}