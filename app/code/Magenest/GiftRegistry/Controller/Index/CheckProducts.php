<?php
/**
 * Created by PhpStorm.
 * User: hung
 * Date: 22/01/2019
 * Time: 11:37
 */
namespace Magenest\GiftRegistry\Controller\Index;

use Magento\Checkout\Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\ProductRepositoryInterface;

class CheckProducts extends \Magento\Framework\App\Action\Action
{
    const ACTION_STAY_PRODUCT_LIST = 1;
    const ACTION_REDIRECT_PRODUCT_DETAIL = 2;
    const ACTION_REDIRECT_TO_ADD = 3;


    protected $resultPageFactory;
    protected $_collectionFactory;
    protected $productRepository;
    protected $dataHelper;
    protected $_urlInterface;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ProductRepositoryInterface $productRepository,
        \Magenest\GiftRegistry\Helper\Data $dataHelper
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->dataHelper = $dataHelper;
        $this->_urlInterface = $this->_url;
    }
    public function execute()
    {
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        $objectManager = ObjectManager::getInstance();
        $customer = $objectManager->create('\Magento\Customer\Model\Session');
        $customerId = $customer->getCustomer()->getId();

        $productId = $this->getRequest()->getParam('productId');
        $product = $this->productRepository->getById($productId);
        $productUrl = $product->getProductUrl();
        $productType = $product->getTypeId();

        $giftRegistryId = $this->dataHelper->getGiftId();

        $haveOneRegistry = $this->dataHelper->getHaveOneRegistry();
        $haveExpiredRegistry = $this->dataHelper->isHaveUnexpiredGift($customerId);
        $url =  $this->_urlInterface->getUrl('giftregistrys/index/add', ['product' => $productId, 'giftregistry' => $giftRegistryId,]);
        if(!$haveOneRegistry){
            if($haveExpiredRegistry){ // gifts isn't actived
                $this->messageManager->addError(__('Events has expired! Please create gift registry before adding item to gift registry.'));
            }else{
                $this->messageManager->addError(__('Customer have to login and create gift registry before adding item to gift registry.'));
            }
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        } else{ //The customer has more 1 registry
            if ($haveOneRegistry > 1) {
                $this->messageManager->addWarning(__('Your must choose what registry need to add item!'));
                $resultRedirect->setUrl($productUrl);
                return $resultRedirect;
            } else{
                if($productType == 'configurable'){
                    $this->messageManager->addWarning(__('Your must choose option need to add item!'));
                    $resultRedirect->setUrl($productUrl);
                    return $resultRedirect;
                } else if($productType == 'simple' || $productType == 'virtual'){
                    if($product->getRequiredOptions()){
                        $this->messageManager->addWarning(__('Your must choose option need to add item!'));
                        $resultRedirect->setUrl($productUrl);
                        return $resultRedirect;
                    }else{
                        $this->messageManager->addSuccess(__('The item is added to your gift registry.'));
                        $resultRedirect->setUrl($url);
                        return $resultRedirect;
                    }
                }
            }

        }
        return $resultJson;
    }
}
