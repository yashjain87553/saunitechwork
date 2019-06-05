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

class CheckProductsDetail extends \Magento\Framework\App\Action\Action
{
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
        $giftRegistryType = $this->dataHelper->getGiftType();
        $haveOneRegistry = $this->dataHelper->getHaveOneRegistry();
        $haveExpiredRegistry = $this->dataHelper->isHaveUnexpiredGift($customerId);
        $url =  $this->_urlInterface->getUrl('giftregistrys/index/add', ['product' => $productId, 'giftregistry' => $giftRegistryId,]);
        $manageUrl = $this->_urlInterface->getUrl("giftregistrys/index/manageregistry/type/".$giftRegistryType."/event_id/".$giftRegistryId);
        if(!$haveOneRegistry){
            if($haveExpiredRegistry){ // gifts isn't actived
                $data = [
                    'messageType' => 'Events has expired! Please create gift registry before adding item to gift registry.'
                ];
            }else{
                $data = [
                    'messageType' => 'Customer have to login and create gift registry before adding item to gift registry.'
                ];
            }
            $resultJson->setData($data);
            return $resultJson;
        } else{ //The customer has more 1 registry
            if ($haveOneRegistry > 1) {
                $giftData = $this->dataHelper->getGiftRegistryByCustomer();
                $data = [
                    'data' => $giftData,
                    'showGift' => true
                ];
                $resultJson->setData($data);
                return $resultJson;
            } else{
                $data = [
                    'showGift' => false,
                    'urlAdd' => $url,
                    'urlManage' => $manageUrl
                ];
                $resultJson->setData($data);
                return $resultJson;
            }

        }
        return $resultJson;
    }
}
