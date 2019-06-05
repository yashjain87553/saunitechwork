<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 21/03/2016
 * Time: 11:50
 */
namespace Magenest\GiftRegistry\Block\Guest;

use Magento\Catalog\Model\Product;
use Zend\Serializer\Serializer;

/**
 * Class View
 * @package Magenest\GiftRegistry\Block\Guest
 */
class View extends \Magenest\GiftRegistry\Block\Customer\Registry\ViewRegistry
{

    protected $configurableType;

    public function __construct(
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magenest\GiftRegistry\Helper\Data $helper,
        \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory $itemFactory,
        \Magenest\GiftRegistry\Model\Config\Priority $priority,
        \Magenest\GiftRegistry\Model\TypeFactory $typeFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
//        \Magento\Catalog\Helper\ImageFactory $imageFactory,
        array $data = []
    )
    {
        $this->configurableType = $configurable;
        parent::__construct($registrantFactory, $context, $formKey, $currentCustomer, $helper, $itemFactory, $priority, $typeFactory, $productFactory, $productRepositoryInterface, $eavAttribute, $data);
    }

    /**
     * Get value pr
     *
     * @param $priority
     * @return mixed
     */
    public function getPriority($priority)
    {
        $config = $this->priority->toOptionArray();

        foreach ($config as $key => $value) {
            if ($key == $priority) {
                return $value;
            }
        }

        return;
    }

    /**
     * @return bool
     */
    public function checkCustomer()
    {
        $customerId=$this->currentCustomer->getCustomerId();
        $giftRegistry=$this->getGiftRegistry();
        $id=$giftRegistry['customer_id'];
        if ($id==$customerId) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * @return mixed
     */
    public function getBaseUrlEvent()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
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
    public function getGiftRegistry()
    {
        return $this->_coreRegistry->registry('registry');
    }

    /**
     * @return mixed
     */
    public function getGiftRegistryItem()
    {
        return $this->_coreRegistry->registry('item');
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return  $this->_formKey->getFormKey();
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        $params=$this->getRequest()->getParams('type');
        return $params['type'];
    }
    /**
     * @param int $productId
     * @return string
     */
    public function addToCart($productId = 0)
    {
        if ($productId) {
            return $this->getUrl('giftregistrys/cart/add', ['product_id' =>$productId]);
        } else {
            return $this->getUrl('giftregistrys/cart/add');
        }
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        $registry = $this->_coreRegistry->registry('registry');
        return $registry->getData('password');
    }

    public function hasAddress()
    {
        if ($this->currentCustomer->getCustomerId() == '') {
            return -1;
        }
        $cusstomerAddress = $this->currentCustomer->getCustomer()->getAddresses();
        if (count($cusstomerAddress) >= 1) {
            return 1;
        }
        return 0;
    }

    public function getLoginUrl()
    {
        return $this->getUrl('customer/account/login');
    }

    public function getMediaImage()
    {
        $event = $this->_typeFactory->create()->getCollection()->addFieldToFilter('event_type', $this->getType())->getFirstItem();
        $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."/magenest/giftregistry/type/image";
        if ($event->getData('image') == '') {
            return null;
        }
        $url .= $event->getData('image');
        return $url;
    }

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getCustomizableOption(\Magenest\GiftRegistry\Model\Item $item)
    {
        /**
         * @var $product \Magento\Catalog\Api\Data\ProductInterface
         */
        $product = $item->getProduct();
        $options = $product->getOptions();
        $responseData = [];
        if(!isset($product->getCustomOptions()['option_ids'])){
            return $responseData;
        }
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
        return $responseData;
    }

    /**
     * @param $product Product
     * @param $item
     * @return string
     */
    public function getImageProduct($product,$item){
        try {
            if ($product->getTypeId() == 'configurable') {
                return $this->getChildProductImageUrl($product, $item);
            }
        }catch (\Exception $exception){

        }
        $imageUrl = $this->_imageHelper->init($product, 'product_thumbnail_image')->getUrl();
        if ($product->getCustomOption('simple_product') && $product->getCustomOption('simple_product')->getProduct()) {
            $imageUrl = $this->getImage($product, 'category_page_list')->getImageUrl();
        }
        return $imageUrl;
    }

    private function getChildProductImageUrl(Product $product, $item){
        $productOption = Serializer::unserialize($item->getBuyRequest())['super_attribute'];
        $childProduct  = $this->configurableType->getProductByAttributes($productOption,$product);
        $imageUrl = $this->_imageHelper->init($childProduct, 'product_thumbnail_image')->getUrl();
        return $imageUrl;
    }

    public function getPrice($item,$product){
        $price = null;
        if ($product->getTypeId() == "configurable") {
            $request = $item->getData('buy_request');
            $request = unserialize($request);
            $products = $product->getTypeInstance()->getUsedProducts($product);
            $options =[];
            foreach ($request['super_attribute'] as $key => $value){
                $options[$key] = $value;
            }
            $attributes=[];
            foreach ($options as $key =>$value ){
                $eavAttribute = $this->eavAttribute->load($key);
                $attributes[$eavAttribute->getAttributeCode()] = $value;
            }

            foreach ($products as $product){
                $check = true;
                $data =$product->getData();
                foreach ($attributes as $key => $attribute){
                    if(!isset($data[$key]) || $data[$key]!==$attribute){
                        $check = false;
                        continue;
                    }
                }
                if($check==true){
                    $id = $data['entity_id'];
                    $currentProduct = $this->_productFactory->create()->load($id);
                    if($currentProduct){
                        $price =$currentProduct->getFinalPrice();
                    }

                }
            }
        } else {
            $price = $product->getFinalPrice();
        }
        return $price;
    }
}
