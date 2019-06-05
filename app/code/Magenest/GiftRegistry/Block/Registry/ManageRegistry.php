<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 13/07/2017
 * Time: 17:39
 */

namespace Magenest\GiftRegistry\Block\Registry;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\TranFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class ManageRegistry
 * @package Magenest\GiftRegistry\Block\Registry
 */
class ManageRegistry extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory
     */
    protected $_itemFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @var \Magenest\GiftRegistry\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magenest\GiftRegistry\Model\Config\Priority
     */
    protected $priority;

    /**
     * @var GiftRegistryFactory
     */
    protected $_registryFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var TranFactory
     */
    protected $_tranFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected $_catalogProductTypeConfigurable;

    protected $eavAttribute;

    protected $storeConfig;
    protected $storeManager;

    /**
     * ManageRegistry constructor.
     * @param \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magenest\GiftRegistry\Model\AddressFactory $addressFactory
     * @param GiftRegistryFactory $registryFactory
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magenest\GiftRegistry\Helper\Data $helper
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory $itemFactory
     * @param \Magenest\GiftRegistry\Model\Config\Priority $priority
     * @param array $data
     */
    public function __construct(
        \Magenest\GiftRegistry\Model\TranFactory $tranFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magenest\GiftRegistry\Model\AddressFactory $addressFactory,
        GiftRegistryFactory $registryFactory,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magenest\GiftRegistry\Helper\Data $helper,
        \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory $itemFactory,
        \Magenest\GiftRegistry\Model\Config\Priority $priority,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        \Magento\Store\Model\StoreManager $storeManager,
        array $data = []
    ) {
        $this->productRepository = $productRepositoryInterface;
        $this->_productFactory = $productFactory;
        $this->_tranFactory = $tranFactory;
        $this->_orderFactory = $orderFactory;
        $this->_registrantFactory = $registrantFactory;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_registryFactory = $registryFactory;
        parent::__construct($context, $data);
        $this->priority = $priority;
        $this->currentCustomer = $currentCustomer;
        $this->_itemFactory = $itemFactory;
        $this->_formKey = $formKey;
        $this->helper = $helper;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->eavAttribute = $eavAttribute;
        $this->_storeConfig = $this->_scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * @return mixed
     */
    public function getBaseUrlEvent()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @param $event
     * @return string
     */
    public function getViewUrl($event)
    {
        return $this->getUrl('giftregistry').'view'.str_replace('gift', '', $this->_coreRegistry->registry('type')).'.html?id='.$this->_coreRegistry->registry('gift_id');
    }

    /**
     * @return mixed
     */
    public function getRegistryId()
    {
        return $this->_coreRegistry->registry('gift_id');
    }

    public function haveOrder()
    {
        $event_id = $this->_coreRegistry->registry('gift_id');
        $orderCollection = $this->_tranFactory->create()->getCollection()->addFieldToFilter('giftregistry_id', $event_id);
        if (count($orderCollection) == 0) {
            return 0;
        }
        return 1;
    }

    /**
     * @return mixed
     */
    public function getOrderCollection()
    {
        $event_id = $this->_coreRegistry->registry('gift_id');
        $orderCollection = $this->_tranFactory->create()->getCollection()->addFieldToFilter('giftregistry_id', $event_id);
        return $orderCollection;
    }

    /**
     * @param $orderID
     * @return mixed
     */
    public function getOrder($orderID)
    {
        return $this->_orderFactory->create()->load($orderID);
    }

    /**
     * @return mixed
     */
    public function getIdEvent()
    {
        $giftRegistryId = $this->_coreRegistry->registry('gift_id');
        $itemsCollection = $this->_itemFactory->create()
            ->addFieldToFilter('gift_id', $giftRegistryId);
        return $itemsCollection;
    }

    /**
     * @return string
     */
    public function getUpdateActionUrl()
    {
        return $this->getUrl('giftregistrys/customer/item');
    }

    /**
     * @return string
     */
    public function getSaveImageUrl()
    {
        return $this->getUrl('giftregistrys/index/upload');
    }

    /**
     * @param $item_id
     * @param $id
     * @return string
     */
    public function getDeleteItemUrl($item_id, $id)
    {
        return $this->getUrl('giftregistrys/customer/item', ['type' => 'delete', 'item_id' => $item_id, 'id' => $id]);
    }

    /**
     * @param $item
     * @return \Magenest\GiftRegistry\Helper\is
     */
    public function getItemOptions($item)
    {
        $options = $this->helper->getOptions($item);
        return $options;
    }

    /**
     * @return mixed
     * Get event type
     */
    public function getEventType()
    {
        return $this->_coreRegistry->registry('type');
    }

    /**
     * @return $this
     */
    public function getItemList()
    {
          return $this->_itemFactory->create()->addFieldToFilter('gift_id', $this->_coreRegistry->registry('gift_id'));
    }

    /**
     * @return $this
     * Get registry via id
     */
    public function getRegistry()
    {
        $params = $this->getRequest()->getParams();
        $giftRegistryId = $this->_coreRegistry->registry('gift_id');
        return $this->_registryFactory->create()->load($giftRegistryId);
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * Get shipping address of customer.
     */
    public function getShippingAddress()
    {
        return $this->_addressFactory->create()->getCollection();
    }

    /**
     * get the customer address
     *
     * @return array
     */
    public function getCustomerAddress()
    {
        $addressArr = [];
        $customerId = $this->currentCustomer->getCustomerId();//->getAddressesCollection();

        $customer = $this->_customerFactory->create()->load($customerId);

        $addressCollection = $customer->getAddressesCollection();

        if ($addressCollection->getSize()) {
            /**
             * @var  $address \Magento\Customer\Model\Address
             */
            foreach ($addressCollection as $address) {
                $addressArr[] = ['name' => $address->getName() . ' ' . $address->getStreetFull() . ' ' . $address->getRegion() . ' ' . $address->getCountry(),
                    'id' => $address->getId()
                ];
            }
        }

        return $addressArr;
    }

    /**
     * @return mixed
     * Get registrant of registry.
     */
    public function getRegistrants()
    {
        $registryId = $this->_coreRegistry->registry('gift_id');
        if ($registryId > 0) {
            $registrants = $this->_registrantFactory->create()->getCollection()->addFieldToFilter('giftregistry_id', $registryId);
        } else {
            $registrants = $this->_registrantFactory->create()->getCollection()->addFieldToFilter('giftregistry_id', -1);
        }
        return $registrants;
    }

    /**
     * @return string
     * Get save url
     */
    public function getSaveAddressUrl()
    {
        return $this->getUrl('giftregistrys/customer/post', ['event_id' => $this->_coreRegistry->registry('gift_id')]);
    }

    /**
     * @return string
     * Get guest view url
     */
    public function getLinkShare()
    {
        $linkShare = $this->getUrl('giftregistry').'view'.str_replace('gift', '', $this->_coreRegistry->registry('type')).'.html?id='.$this->_coreRegistry->registry('gift_id');
        return $linkShare;
    }

    /**
     * @return string
     */
    public function getGiftTitle()
    {
        $id = $this->_coreRegistry->registry('gift_id');
        $gift = $this->_registryFactory->create()->load($id, 'gift_id');
        return $gift ? $gift->getTitle() : "";
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $id = $this->_coreRegistry->registry('gift_id');
        $gift = $this->_registryFactory->create()->load($id, 'gift_id');
        return $gift ? $gift->getDescription() : "";
    }

    /**
     * @return string
     */
    public function getSendMailUrl()
    {
        return $this->getUrl('giftregistrys/guest/checkemail');
    }

    /**
     * Ajax delete event
     *
     * @return string
     */
    public function deleteEvent()
    {
        return $this->getUrl('giftregistrys/customer/delete');
    }

    /**
     * @return string
     */
    public function getListGiftUrl()
    {
        return $this->getUrl().'giftregistry.html';
    }

    /**
     * @param $type
     * @return string
     */
    public function getImagebyType($type)
    {
        switch ($type) {
            case 'babygift':
                return 'panel-manage-baby.jpeg';
            case 'weddinggift':
                return 'panel-manage-wedding.jpeg';
            case 'birthdaygift':
                return 'panel-manage-birthday.jpeg';
            default:
                return 'panel-manage-christmas.jpeg';
        }
    }

    /**
     * @param $type
     * @return string
     */
    public function getImageAvatarbyType($type)
    {
        switch ($type) {
            case 'babygift':
                return 'avatar-babygift.png';
            case 'weddinggift':
                return 'avatar-wedding.jpg';
            case 'birthdaygift':
                return 'avatar-birthday.jpg';
            default:
                return 'avatar-christmas.jpg';
        }
    }

    /**
     * @return null|string
     */
    public function getMediaImage()
    {
        $registry = $this->getRegistry();
        $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."/magenest/giftregistrys/type/image";
        if ($registry->getData('image') == '') {
            return null;
        }
        $url .= $registry->getData('image');
        return $url;
    }

    /**
     * @return string
     */
    public function getResetImageUrl()
    {
        return $this->getUrl('giftregistrys/index/reset', ['event_id' => $this->_coreRegistry->registry('gift_id'),'type' => $this->getEventType()]);
    }

    /**
     * @param $options
     * @param $name
     * @param $itemID
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProductByOption($options, $name, $itemID)
    {
        $products = $this->_productFactory->create()->getCollection();
        $products = $products->addFieldToFilter('name', ['like' => '%'.$name.'%']);
        foreach ($options as $option) {
            $products = $products->addFieldToFilter('name', ['like' => '%'.$option['value'].'%']);
        }
        $product = $products->getFirstItem();
        if($product->getData('entity_id')) {
            $result = $this->productRepository->getById($product->getData('entity_id'));
            $customOptionsArr = $this->helper->getCustomOptionAsArr($itemID);
            $result->setCustomOptions($customOptionsArr);
            return $result;
        }else{
            return $product;
        }
    }

    public function getCustomizableOption(\Magenest\GiftRegistry\Model\Item $item)
    {
        /**
         * @var $product \Magento\Catalog\Api\Data\ProductInterface
         */
        $product = $item->getProduct();
        $options = $product->getOptions();
        $responseData = [];
        $customOptions = $product->getCustomOptions();
        if(@$customOptions['option_ids']){
            $optionIds = explode(',', $product->getCustomOptions()['option_ids']->getData('value'));
            $optionTypeIds = [];
            foreach ($optionIds as $id) {
                array_push($optionTypeIds, $product->getCustomOptions()['option_'.$id]->getData('value'));
            }
            $i = 0;
            foreach ($options as $option) {
                $optionData = $option->getValues();
                foreach ($optionData as $data) {
                    if (in_array($data->getData('option_type_id'), $optionTypeIds)) {
                        array_push($responseData, array('label' => $product->getData('options')[$i++]->getData('title'), 'value' => $data->getData('title')));
                        break;
                    }
                }
            }
        }
        return $responseData;
    }

    public function getImageProduct($product,$item){

        $id = $product->getEntityId();
        $parentByChild = $this->_catalogProductTypeConfigurable->getParentIdsByChild($id);
        if(isset($parentByChild[0])){
            $id = $parentByChild[0];
            $url = $this->getImage($product, 'category_page_list')->getImageUrl();
            if (strlen(strstr($url, "small_image")) > 0) {
                $product = $this->_productFactory->create()->load($id);
                $url = $this->getCustomImageUrl($product, 'category_page_list', $item);
            }
        } else {
            $url = $this->getCustomImageUrl($product, 'category_page_list', $item);
        }

        return $url;
    }

    public function checkProduct($product){
        $id = $product->getEntityId();
        $parentByChild = $this->_catalogProductTypeConfigurable->getParentIdsByChild($id);
        if(isset($parentByChild[0])){
            $id = $parentByChild[0];
            $product = $this->_productFactory->create()->load($id);
              return $product;
        }
        return $product;
    }

    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        return parent::getProductPrice($product); // TODO: Change the autogenerated stub
    }

    public function getPrice($item, $product)
    {
        $price = null;
        if ($product->getTypeId() == "configurable") {
            $request = $item->getData('buy_request');
            $request = unserialize($request);
            $products = $product->getTypeInstance()->getUsedProducts($product);
            $options = [];
            foreach ($request['super_attribute'] as $key => $value) {
                $options[$key] = $value;
            }
            $attributes = [];
            foreach ($options as $key => $value) {
                $eavAttribute = $this->eavAttribute->load($key);
                $attributes[$eavAttribute->getAttributeCode()] = $value;
            }

            foreach ($products as $product) {
                $check = true;
                $data = $product->getData();
                foreach ($attributes as $key => $attribute) {
                    if (!isset($data[$key]) || $data[$key] !== $attribute) {
                        $check = false;
                        continue;
                    }
                }
                if ($check == true) {
                    $id = $data['entity_id'];
                    $currentProduct = $this->_productFactory->create()->load($id);
                    if ($currentProduct) {
                        $price = $currentProduct->getFinalPrice();
                    }

                }
            }
        } else {
            $price = $product->getFinalPrice();
        }
        return $price;
    }

    public function getImage($product, $imageId, $attributes = [])
    {
        if ($product->getCustomOption('simple_product') && $product->getCustomOption('simple_product')->getProduct()) {
            return parent::getImage($product, $imageId, $attributes);
        }
        return '';
    }

    public function getCustomImageUrl($product, $imageId, $item = null, $attributes = []){
           try {
               if($product->getTypeId() != 'configurable'){
                   return parent::getImage($product, $imageId, $attributes)->getImageUrl();
               }else{
                   if($product->getImage() && $product->getImage() != 'no_selection'){
                       $image = $this->getUsedImageChildProduct($item, $product);
                       return $this->getUrl('pub/media/catalog').'product'.$image;
                   }
               }
           }catch (\Exception $exception){

           }
        return '';
    }

    public function getIsExpiredGift()
    {
        $registryId = $this->_coreRegistry->registry('gift_id');
        if($this->_registryFactory->create()->load($registryId)->getData('is_expired') == 0) {
            return false;
        }
        return true;
    }

    public function getUsedImageChildProduct($item, $product)
    {
        $request = $item->getData('buy_request');
        $request = unserialize($request);
        $products = $product->getTypeInstance()->getUsedProducts($product);
        $options = [];
        foreach ($request['super_attribute'] as $key => $value) {
            $options[$key] = $value;
        }
        $attributes = [];
        foreach ($options as $key => $value) {
            $eavAttribute = $this->eavAttribute->load($key);
            $attributes[$eavAttribute->getAttributeCode()] = $value;
        }

        foreach ($products as $product) {
            $check = true;
            $data = $product->getData();
            foreach ($attributes as $key => $attribute) {
                if (!isset($data[$key]) || $data[$key] !== $attribute) {
                    $check = false;
                    continue;
                }
            }
            if ($check == true) {
                $id = $data['entity_id'];
                $currentProduct = $this->_productFactory->create()->load($id);
                if ($currentProduct) {
                    $image = $currentProduct->getImage();
                }
            }
        }
        return $image;
    }

}
