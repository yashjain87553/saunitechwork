<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 01/12/2015
 * Time: 14:10
 */

namespace Magenest\GiftRegistry\Block\Customer\Registry;

/**
 * Class ViewRegistry
 * @package Magenest\GiftRegistry\Block\Customer\Registry
 */
class ViewRegistry extends \Magento\Catalog\Block\Product\AbstractProduct
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
    protected $_formKey ;

    /**
     * @var \Magenest\GiftRegistry\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magenest\GiftRegistry\Model\Config\Priority
     */
    protected $priority;

    /**
     * @var \Magenest\GiftRegistry\Model\TypeFactory
     */
    protected $_typeFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected $registrantFactory;

    protected $eavAttribute;

    /**
     * ViewRegistry constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magenest\GiftRegistry\Helper\Data $helper
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory $itemFactory
     * @param \Magenest\GiftRegistry\Model\Config\Priority $priority
     * @param \Magenest\GiftRegistry\Model\TypeFactory $typeFactory
     * @param array $data
     */
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
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->registrantFactory = $registrantFactory;
        $this->productRepository = $productRepositoryInterface;
        $this->_productFactory = $productFactory;
        $this->_typeFactory = $typeFactory;
        $this->priority = $priority;
        $this->currentCustomer = $currentCustomer;
        $this->_itemFactory = $itemFactory;
        $this->_formKey = $formKey;
        $this->helper = $helper;
        $this->eavAttribute = $eavAttribute;
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
    public function getIdEvent()
    {
        $params = $this->getRequest()->getParams();

        $giftRegistryId =(isset($params['id'])) ?$params['id']:$params['event_id'];
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
     * @param $item_id
     * @param $id
     * @return string
     */
    public function getDeleteItemUrl($item_id, $id)
    {
        return $this->getUrl('giftregistrys/customer/item', ['type'=>'delete' , 'item_id' =>$item_id ,'id' => $id]);
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
     * @return string
     */
    public function getEditAddress()
    {
        return $this->getUrl('customer/address/edit');
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

    public function getStockItem($item, $_product)
    {
        $productStockObj = null;
//        $products = $this->_productFactory->create()->getCollection();
//        $products = $products->addFieldToFilter('name', ['like' => '%'.$_product->getName().'%']);
        if ($_product->getTypeId() == "configurable") {
            $request = $item->getData('buy_request');
            $request = unserialize($request);
            $products = $_product->getTypeInstance()->getUsedProducts($_product);
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
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $productStockObj = (int)$objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($id)->getData('qty');
                }

            }
        } else {
//            $product = $products->addFieldToFilter("sku", $_product->getSku())->getFirstItem();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productStockObj = (int)$objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($_product->getId())->getData('qty');
        }

        return  $productStockObj;
    }

    public function getRegistrant()
    {
        $params = $this->getRequest()->getParams();
        $giftRegistryId =(isset($params['id'])) ?$params['id']:$params['event_id'];
        $registrant = $this->registrantFactory->create()->getCollection()->addFieldToFilter("giftregistry_id", $giftRegistryId)->getFirstItem();
        return $registrant;
    }
}
