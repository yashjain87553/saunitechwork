<?php
/**
 * Created by PhpStorm.
 * User: ninhvu
 * Date: 18/08/2018
 * Time: 13:05
 */
namespace Magenest\GiftRegistry\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\GiftRegistry\Model\RegistrantFactory;

/**
 * Class Item
 * @package Magenest\GiftRegistry\Observer\Order
 */
class Creditmemo implements ObserverInterface
{

    protected $tranFactory;

    protected $productFactory;

    protected $item;

    protected $eavAttribute;

    public function __construct(
        \Magenest\GiftRegistry\Model\TranFactory $tranFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magenest\GiftRegistry\Model\ItemFactory $itemFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute

   ) {
        $this->tranFactory = $tranFactory;
        $this->productFactory = $productFactory;
        $this->item = $itemFactory;
        $this->eavAttribute = $eavAttribute;
   }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditmemo = $observer->getCreditmemo();
        $orderId = $creditmemo->getOrderId();
        $tranFactory = $this->tranFactory->create()->getCollection()->addFieldToFilter("order_id",$orderId)->getFirstItem();
        // order co trong gift registry
        if($tranFactory){
            $giftregistryId = $tranFactory->getGiftregistryId();
            $items = $creditmemo->getData('items');
            $totalQty = $creditmemo->getTotalQty();
            $check = "NotConfigurable";
            foreach ($items as $item){
                if($totalQty==0){
                    break;
                }
                $qty=0;
                if($item->getQty()>0){
                    $qty = $item->getQty();
                    $productId = $item->getProductId();
                    $product = $this->productFactory->create()->load($productId);
                    if($product){
                        $type = $product->getTypeId();
                        if($type=="configurable"){
                            $check ="Configurable";
                            $parentProduct = $product;
                        } else {
                            if($check=="Configurable"){
                                $itemGift = $this->item->create()->getCollection()->addFieldToFilter("gift_id",$giftregistryId)
                                    ->addFieldToFilter("product_id",$parentProduct->getEntityId());
                                if($itemGift->getSize()>0){
                                    foreach ($itemGift as $itemGiftRegistry){
                                        $idChildProduct = $this->getChildProduct($itemGiftRegistry,$parentProduct);
                                        if($idChildProduct){
                                            if($idChildProduct==$productId){
                                                $qtyGift = $itemGiftRegistry->getReceivedQty()-$qty;
                                                if($qtyGift<0){
                                                    $qtyGift=0;
                                                }
                                                $itemGiftRegistry->setReceivedQty($qtyGift);
                                                $itemGiftRegistry->save();
                                            }
                                        }

                                    }
                                }
                                $check = "NotConfigurable";
                            }
                            $itemGift = $this->item->create()->getCollection()->addFieldToFilter("gift_id",$giftregistryId)
                                                           ->addFieldToFilter("product_id",$productId)->getFirstItem();
                            if($itemGift){
                                $qtyGift = $itemGift->getReceivedQty()-$qty;
                                if($qtyGift<0){
                                    $qtyGift=0;
                                }
                                $itemGift->setReceivedQty($qtyGift);
                                $itemGift->save();
                                $totalQty = $totalQty-$qty;
                            }
                        }
                    }

                }
            }
        }

    }

    public function getChildProduct($item,$_product){
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
                return $data['entity_id'];

            }

        }
        return;
   }

   public function checkParentProduct($productId){
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       $product = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($productId);
       if(isset($product[0])){
        return $product[0];
       }
       return;
   }

}
