<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 30/11/2015
 * Time: 11:40
 */

namespace Magenest\GiftRegistry\Helper;


/**
 * Class Data
 * @package Magenest\GiftRegistry\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Currently logged in customer
     *
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $_currentCustomer;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;


    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magenest\GiftRegistry\Model\GiftRegistryFactory
     */
    protected $_giftRegistryFactory;

    /**
     * @var \Magenest\GiftRegistry\Controller\GiftRegistryProviderInterface
     */
    protected $_giftRegistryProvider;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magenest\GiftRegistry\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\Item\OptionFactory
     */
    protected $optionFactory;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $configurationHelper;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    protected $loger;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory
     * @param \Magenest\GiftRegistry\Model\ItemFactory $itemFactory
     * @param \Magenest\GiftRegistry\Model\Item\OptionFactory $optionFactory
     * @param \Magento\Catalog\Helper\Product\Configuration $configuration
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magenest\GiftRegistry\Controller\GiftRegistryProviderInterface $giftRegistryProviderInterface
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory,
        \Magenest\GiftRegistry\Model\ItemFactory $itemFactory,
        \Magenest\GiftRegistry\Model\Item\OptionFactory $optionFactory,
        \Magento\Catalog\Helper\Product\Configuration $configuration,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magenest\GiftRegistry\Controller\GiftRegistryProviderInterface $giftRegistryProviderInterface,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
    
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_postDataHelper = $postDataHelper;
        $this->productRepository = $productRepository;

        $this->_giftRegistryFactory = $giftRegistryFactory;
        $this->_giftRegistryProvider = $giftRegistryProviderInterface;

        $this->itemFactory = $itemFactory;
        $this->optionFactory = $optionFactory;
        $this->configurationHelper = $configuration;
        parent::__construct($context);
    }

    public function isAllow()
    {
        return true;
    }

    /**
     * if customer not logged in return 0 or customer logged in and have no gift registry return 0 too
     * If customer has logged in and have 1 gift registry return 1
     * if customer has loggerd in and have more than 1 gift registry return 2
     *
     * @return int
     */
    public function getHaveOneRegistry()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create("Magento\Customer\Model\Session");
        $params = [];
        $customerId = $customer->getId();
        if (is_object($customer) && $customerId) {
            $collection = $this->_giftRegistryFactory->create()->getAllGiftRegistryByCustomerId($customerId);
            $size = $collection->getSize();
            if ($size == 0) {
                return 0;
            } else {
                return $size;
            }
        } else {
            return 0;
        }
    }

    public function getGiftIdsAsString()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create("Magento\Customer\Model\Session");
        $params = [];

        $customerId = $customer->getId();
        if (is_object($customer) && $customerId) {
            $this->_logger->critical("Id la:".$customerId);
            $collection = $this->_giftRegistryFactory->create()->getAllGiftRegistryByCustomerId($customerId);
            if ($collection->getSize() > 0) {
                foreach ($collection as $giftRegistry) {
                    $params[] = $giftRegistry->getId();
                }
            }
        }

        return implode(',', $params);
    }

    /**
     * @return $this|array
     */
    public function getGiftIds()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create("Magento\Customer\Model\Session");
        $params = [];

        $customerId = $customer->getId();
        if (is_object($customer) && $customerId) {
            $collection = $this->_giftRegistryFactory->create()->getAllGiftRegistryByCustomerId($customerId);
            return $collection;
        }
        return $params;
    }

    public function getIdsRegistry(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create("Magento\Customer\Model\Session");
        $params = [];
        $customerId = $customer->getId();
        if (is_object($customer) && $customerId) {
            $collection = $this->_giftRegistryFactory->create()->getAllGiftRegistryByCustomerId($customerId);
            if($collection->getSize()>0){
                foreach ($collection as $giftRegistry){
                    $params[]=$giftRegistry->getId();
                }
            }
        }

        return $params;

    }


    /**
     * @return int
     */
    public function getGiftId()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create("Magento\Customer\Model\Session");
        $giftRegistryId = 0;
        $customerId = $customer->getId();
        if (is_object($customer) && $customerId) {

            $collection = $this->_giftRegistryFactory->create()->getAllGiftRegistryByCustomerId($customerId);

            if ($collection->getSize() > 0) {
                $giftRegistryId = $collection->getFirstItem()->getId();
            }
        }
        return $giftRegistryId;
    }

    public function getGiftType()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create("Magento\Customer\Model\Session");
        $customerId = $customer->getId();
        if (is_object($customer) && $customerId) {

            $collection = $this->_giftRegistryFactory->create()->getAllGiftRegistryByCustomerId($customerId);
            $giftRegistryType = null;
            if ($collection->getSize() > 0) {
                $giftRegistryType = $collection->getFirstItem()->getType();
            }
            return $giftRegistryType;
        }
        return '';
    }

    /**
     * Retrieve current customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        $customerSession = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\Customer\Model\Session::class);
        if ($customerSession->isLoggedIn()) {
            return $customerSession->getCustomerDataObject();
        }
        return null;
    }

    /**
     * Check whether customer logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        if ($this->_customerSession->isLoggedIn()) {
            return true;
        }
        return false;
    }

    /**
     * Get params to add item to gift registry
     *
     * @param  $item
     * @param  array $params
     * @return string
     */
    public function getAddParams($item, $giftId, array $params = [])
    {
        $params = [];
        $params['giftregistry'] = $giftId;
        $productId = null;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $productId = $item->getEntityId();
        }

        if (method_exists($item, 'getProductId')) {
            $productId = $item->getProductId();
        }

        $store = $this->_storeManager->getStore();
        $url = $store->getUrl('giftregistrys/index/add');
        if ($productId) {
            $params['product'] = $productId;
        }

        return $this->_postDataHelper->getPostData($url, $params);
    }

    /**
     * @param $itemId
     * @return array
     */
    public function getOptionByCode($itemId, $code)
    {
        $optionArr = [];
        $optionCollection = $this->optionFactory->create()->getCollection()->addFieldToFilter('gift_item_id', $itemId)
            ->addFieldToFilter('code', $code);

        if ($optionCollection->getSize() > 0) {
            foreach ($optionCollection as $option) {
                $optionArr[$option->getCode()] = $option;
            }
        }

        // var_dump($optionArr);
        return $optionArr;
    }

    public function getOptions($item)
    {
        /**
         * @var  $options is array contains string key and value
         */
        $options = $this->configurationHelper->getOptions($item);
        return $options;
    }

    public function getCustomOptionAsArr($itemId)
    {
        $optionArr = [];
        $optionCollection = $this->optionFactory->create()->getCollection()->addFieldToFilter('gift_item_id', $itemId);

        if ($optionCollection->getSize() > 0) {
            foreach ($optionCollection as $option) {
                $optionArr[$option->getCode()] = $option;
            }
        }

        return $optionArr;
    }

    public function getTypeLabel($type)
    {
        switch ($type) {
            case 'babygift':
                return "Add to Your Baby Registry";
            case 'weddinggift':
                return "Add to Your Wedding Registry";
            case 'birthdaygift':
                return "Add to Your Birthday Registry";
            case 'christmasgift':
                return "Add to Your Christmas Registry";
            default:
                return '';
        }
    }

    public function getCreateAccountUrl()
    {
        $store = $this->_storeManager->getStore();
        $url = $store->getUrl('giftregistrys/index/listgift');
        return $url;
    }

    public function isHaveUnexpiredGiftByStatus($customerId,$type)
    {
        $giftRegistry = $this->_giftRegistryFactory->create()->getCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('is_expired', 0);
        if($giftRegistry->count() > 0){
            return true;
        }
        return false;
    }

    public function isHaveUnexpiredGiftByDate($customerId,$type)
    {
        $giftRegistry = $this->_giftRegistryFactory->create()->getCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('is_expired', 0)
            ->addFieldToFilter('date', ['gteq' => date('Y-m-d')]);
        if($giftRegistry->count() > 0){
            return true;
        }
        return false;
    }

    public function updateExpiredGift()
    {
        $giftModel = $this->_giftRegistryFactory->create();
        $expiredGiftIds = $this->_giftRegistryFactory->create()->getCollection()
            ->addFieldToFilter('date', ['lt' => date('Y-m-d')])
            ->addFieldToFilter('is_expired', 0)
            ->getAllIds();
        foreach ($expiredGiftIds as $key => $expiredGiftId){
            $giftModel->load($expiredGiftId);
            $giftModel->setData('is_expired', 1);
            $giftModel->save();
        }
    }

    public function isQuoteContainExpiredEventItem($items)
    {
        foreach ($items as $item){
            $buyRequest = $item->getBuyRequest()->getData();
            if(isset($buyRequest['registry'])){
                $registryId = $buyRequest['registry'];
                $registry = $this->_giftRegistryFactory->create()->load($registryId);
                if($registry->getIsExpired()){
                    return true;
                }
            }
        }
        return false;
    }

    public function getGiftRegistryByCustomer()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create("Magento\Customer\Model\Session");
        $customerId = $customer->getId();
        if (is_object($customer) && $customerId) {
            $giftRegistry = $this->_giftRegistryFactory->create();
            $giftData = $giftRegistry->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('is_expired', 0)
                ->getData();
            return $giftData;
        }
        return [];
    }

    public function isHaveUnexpiredGift($customerId)
    {
        $giftRegistry = $this->_giftRegistryFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('is_expired', 1);
        if($giftRegistry->count() > 0){
            return true;
        }
        return false;
    }
}
