<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 19/04/2016
 * Time: 12:00
 */

namespace Magenest\GiftRegistry\Controller\Guest;

use Magento\Framework\App\ResponseInterface;
use Magenest\GiftRegistry\Model\TypeFactory as TypeFactory;

/**
 * Class View
 * @package Magenest\GiftRegistry\Controller\Guest
 */
class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory
     */
    protected $_itemFactory;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $_currentCustomer;

    /**
     * @var \Magenest\GiftRegistry\Model\GiftRegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var TypeFactory
     */
    protected $_typeFactory;

    protected $assetRepo;

    /**
     * View constructor.
     * @param \Magenest\GiftRegistry\Model\GiftRegistryFactory $registryFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory $itemFactory
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        TypeFactory $typeFactory,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $registryFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory $itemFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {

        $this->_typeFactory = $typeFactory;
        $this->_context = $context;
        $this->_currentCustomer = $currentCustomer;
        $this->registryFactory = $registryFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_itemFactory = $itemFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->assetRepo = $assetRepo;
        parent::__construct($context);
    }


    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $giftRegistryId =(isset($params['id'])) ?$params['id']:$params['event_id'];
        $model =$this->registryFactory->create()->load($giftRegistryId);
        if ($model->getData() == null) {
            $resultPage = $this->resultRedirectFactory->create()->setPath("giftregistrys/index/listgift");
            $this->messageManager->addNotice("This event has been deleted! Please contact to the producer!");
            return $resultPage;
        }
        if ($model->getData('is_expired')) {
            $resultPage = $this->resultRedirectFactory->create()->setPath("giftregistrys/index/listgift");
            $this->messageManager->addNotice("This event has expired! Please contact to the producer!");
            return $resultPage;
        }
        $itemsCollection = $this->_itemFactory->create()
            ->addFieldToFilter('gift_id', $giftRegistryId);
        $this->_coreRegistry->register('registry', $model);
        $this->_coreRegistry->register('item', $itemsCollection);
        $type = $this->_typeFactory->create()->getCollection()->addFieldToFilter('event_type', $params['type'])->getFirstItem();
        if ($type->getData("status") == 0) {
            $resultPage = $this->resultRedirectFactory->create()->setPath("giftregistrys/index/listgift");
            $this->messageManager->addNotice("This event has been disabled! Please contact to the producer!");
            return $resultPage;
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__($model->getTitle()));
        $registry = $this->_coreRegistry->registry("registry");
        $image = $registry->getData("image");
        $type = $registry->getData('type');
        $resultPage->getConfig()->setDescription($registry->getData("description"));

        if($image){
            $imageUrl = $this->assetRepo->getUrl("Magenest_GiftRegistry::".$image);
            $resultPage->getConfig()->setMetadata("image",$this->assetRepo->getUrl("Magenest_GiftRegistry::".$image));
        } else {
            if($type=="babygift"){
                $imageUrl = $this->assetRepo->getUrl("Magenest_GiftRegistry::images/guest-view/guestbaby.jpg");
                $resultPage->getConfig()->setMetadata("image",$imageUrl);
            } elseif ($type="weddinggift"){
                $imageUrl = $this->assetRepo->getUrl("Magenest_GiftRegistry::images/guest-view/guestwedding.jpg");
                 $resultPage->getConfig()->setMetadata("image",$imageUrl);
            } elseif ($type=="christmasgift"){
                $imageUrl = $this->assetRepo->getUrl("Magenest_GiftRegistry::images/guest-view/guestchismas.jpeg");
                $resultPage->getConfig()->setMetadata("image",$imageUrl);
            } elseif ($type=="birthdaygift"){
                $imageUrl = $this->assetRepo->getUrl("Magenest_GiftRegistry::images/guest-view/guestbirthday.jpg");
                $resultPage->getConfig()->setMetadata("image",$imageUrl);
            }
        }

        return $resultPage;
    }

    function getDescription(){

    }
}
