<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 25/05/2016
 * Time: 23:50
 */

namespace Magenest\GiftRegistry\Model;

/**
 * Class GiftRegistry
 * @package Magenest\GiftRegistry\Model
 */
class GiftRegistry extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var Item\OptionFactory
     */
    protected $optionFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $_imageBuilder;

    protected $_eventObject = "gift_registry";

    protected $_eventPrefix = "gift_registry";

    /**
     * GiftRegistry constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\GiftRegistry $resource
     * @param ResourceModel\GiftRegistry\Collection $resourceCollection
     * @param ItemFactory $itemFactory
     * @param Item\OptionFactory $optionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry $resource,
        \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\Collection $resourceCollection,
        \Magenest\GiftRegistry\Model\ItemFactory $itemFactory,
        \Magenest\GiftRegistry\Model\Item\OptionFactory $optionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_imageBuilder = $imageBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->itemFactory = $itemFactory;
        $this->optionFactory = $optionFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * @param $product \Magento\Catalog\Model\Product
     * @param $request \Magento\Framework\DataObject
     */
    public function addNewItem($product, $request)
    {
        $cartCandidates = $product->getTypeInstance()->processConfiguration($request, clone $product);
        if($request['selected_configurable_option']!=""){
            $idProduct = $request['selected_configurable_option'];
        }
        foreach ($cartCandidates as $candidate) {
            if(isset($idProduct) && $idProduct!=""){
                $candicateId = $candidate->getEntityId();
                if($idProduct==$candicateId){
                    continue;
                }
            }
            $productPrice = $candidate->getPrice();
            $price = $candidate->getFinalPrice();
            $parentProductId = $candidate->getParentProductId();

            if (!$productPrice || !$price || $parentProductId) {
                continue;
            }

            if ($candidate->getQty()) {
                $qty = $candidate->getQty();
            } else {
                $qty =1;
            }
            //check if the item has ben selected
            $giftId = $request->getGiftregistry();
            $productId = $candidate->getId();
            $_items = $this->itemFactory->create()->getCollection()
                ->addFieldToFilter('gift_id', $giftId)->addFieldToFilter('product_id', $productId);
            $hasDuplicate = false;
            foreach ($_items as $_item) {
                $_itemId = $_item->getGiftItemId();
                $_options = $this->optionFactory->create()->getCollection()->addFieldToFilter('gift_item_id', $_itemId);
                $i = 0;
                foreach ($_options as $_option) {
                    $option1[$i] = $_option->getData('value');
                    $i++;
                }
                $i = 0;
                foreach ($candidate->getCustomOptions() as $optionCode => $value) {
                    $option2[$i] = $value->getValue();
                    $i++;
                }
                $check = true;
                if(count($option1) > 1){
                    if(count($option2) == 1) {
                        $check = false;
                    }
                }
                for ($tmp = 1; $tmp < $i; $tmp++) {
                    try{
                        if (strcmp($option1[$tmp], $option2[$tmp])!=0) {
                            $check = false;
                            break;
                        }
                    }catch(\Exception $e){
                        $check = false;
                        break;
                    }
                }
                if ($check == true) {
                    $hasDuplicate = true;
                    $_item->setData('qty', $_item->getData('qty')+$qty);
                    $_item->save();
                }
//                if(!@$candidate->getCustomOptions()['option_ids']){
//                    $hasDuplicate = false;
//                }
            }
            //add the item to the gift registry item
            if (!$hasDuplicate) {
                $price = $candidate->getFinalPrice();

                $itemData = [
                    'product_id'=> $candidate->getId(),
                    'product_name' => $candidate->getName(),
                    'store_id'  =>$this->storeManager->getStore()->getId(),
                    'gift_id'   =>$request->getGiftregistry(),
                    'qty' => $qty,
                    'final_price' =>$price,
                    'buy_request' => $request->getData()
                ];

                /**
                 * @var  $options array
                 */
                $options = $candidate->getCustomOptions();
                $itemData['buy_request'] = serialize($request->getData());


                $giftRegistryItem = $this->itemFactory->create();

                $giftRegistryItem->setData($itemData)->save();

                //save the option of the item in the table
                if ($giftRegistryItem->getId()) {
                    if (is_array($options) && !empty($options)) {
                        foreach ($options as $optionCode => $option) {
                            $optionModel = $this->optionFactory->create();

                            $optionModel->setData('gift_item_id', $giftRegistryItem->getId());
                            $optionModel->setData('code', $optionCode);
                            $optionModel->setData('product_id', $option->getProductId());
                            $optionModel->setData('value', $option->getValue());
                            $optionModel->save();
                        }
                    }
                }

                $items[] = $giftRegistryItem;
            }
        }
    }

    /**
     * @param null $customerId
     * @return $this
     */
    public function getAllGiftRegistryByCustomerId($customerId = null)
    {
        if ($customerId) {
            $collection =  $this->getResourceCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('is_expired', 0);
            return $collection;
        }
    }

    public function getImage($product, $imageId)
    {
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->create();
    }

    public function sendEmail($order, $params = null)
    {
        if ($order) {
            $templateId = $this->_scopeConfig->getValue(
                'giftregistry/email/template',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
//            $this->_logger->debug($templateId);
            $storeId = $order->getData('store_id');
//            $this->_logger->debug($storeId);
            $recipientEmail = $params['recipient_email'];
//            $this->_logger->debug($recipientEmail);
            $recipientName = $params['recipient_name'];
//            $this->_logger->debug($recipientName);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $_product = $objectManager->get('Magento\Catalog\Model\Product')->load($params['product_id']);
            $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
            $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $_product->getImage();
//            $this->_logger->debug($imageUrl);
            $this->inlineTranslation->suspend();
            $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )->setTemplateVars(
                [
                    'recipient_name' => $recipientName,
                    'giver_name' => $params['giver_name'],
                    'product_name' => $params['product_name'],
                    'qty' => $params['qty'],
                    'image_url' => $imageUrl,
                ]
            )->setFrom(
                $this->_scopeConfig->getValue(
                    'giftregistry/email/sender',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->addTo(
                $recipientEmail,
                $recipientName
            )->getTransport();
            try {
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            } catch (\Magento\Framework\Exception\MailException $e) {
                $this->_logger->critical($e->getMessage());
            };
        }
        return $this;
    }

    public function loadByCustomerId($customerId)
    {
        if ($customerId === null) {
            return $this;
        }
        $customerId = (int)$customerId;
        $this->_getResource()->load($this, $customerId, 'customer_id');


        return $this;
    }
}
