<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 13/07/2017
 * Time: 17:39
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Model\TypeFactory as TypeFactory;

/**
 * Class ManageRegistry
 * @package Magenest\GiftRegistry\Controller\Index
 */
class ManageRegistry extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magenest\GiftRegistry\Model\GiftRegistryFactory
     */
    protected $_registryFactory;

    /**
     * @var TypeFactory
     */
    protected $_typeFactory;

    protected $resultPage;


    /**
     * ReigstryView constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(
        TypeFactory $typeFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $registryFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {

        $this->_typeFactory = $typeFactory;
        $this->_registryFactory = $registryFactory;
        $this->_logger = $logger;
        $this->_customerSession = $session;
        $this->_pageFactory = $pageFactory;
        $this->_coreRegistry = $registry;
        return parent::__construct($context);
    }

    public function getRegistryId()
    {
        return $this->getRequest()->getParam('event_id', null);
    }

    public function getType()
    {
        return $this->getRequest()->getParam('type', null);
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        if ($this->getType() && $type = $this->checkEvent($this->getType())) {
            $this->_coreRegistry->register('type', $type->getData('event_type'));
            if ($this->getCustomerId()) {
                if (!$this->checkCustomerRegistry()) {
                    $this->messageManager->addNotice("You have not created this event!");
                    $this->resultPage = $this->resultRedirectFactory->create()->setPath("giftregistrys/index/listgift");
                    return $this->resultPage;
                }
            } else {
                $this->messageManager->addWarning(__('Please login to continue'));
                $this->_customerSession->setRegistryLogin(true);
                $this->_customerSession->setRegistryType($type->getData('event_type'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('customer/account/login');
            }
            $this->resultPage =  $this->_pageFactory->create();
            $this->resultPage ->getConfig()->getTitle()->set(__($type->getData('event_title')));
            return $this->resultPage;
        } else {
            $this->_coreRegistry->unregister('gift_id');
            $this->_coreRegistry->unregister('type');
            if (!$this->getType()) {
                $this->messageManager->addNotice("Something went wrong with your accession!");
            }
            $this->resultPage = $this->resultRedirectFactory->create()->setPath("giftregistrys/index/listgift");
            return $this->resultPage;
        }
    }

    public function checkEvent($type)
    {
        $typeCollection = $this->_typeFactory->create()->getCollection()->addFieldToFilter('event_type', $type);
        if ($typeCollection->getSize() && $typeCollection->getFirstItem()->getData('status')) {
            return $typeCollection->getFirstItem();
        } else {
            $this->messageManager->addNotice("This event has no longer exists or has been disabled!");
            return false;
        }
    }

    public function checkCustomerRegistry()
    {
        $giftCustomer =  $this->_registryFactory->create()->getCollection()->addFieldToFilter('type', $this->getType())->addFieldToFilter('customer_id', $this->getCustomerId());
        if ($giftCustomer->count() > 0) {
            if($this->getRequest()->getParam('event_id')){
                $giftId = $this->getRequest()->getParam('event_id');
            }else{
                $giftId = $this->_registryFactory->create()->getCollection()
                    ->addFieldToFilter('is_expired', 0)
                    ->addFieldToFilter('type', $this->getType())
                    ->addFieldToFilter('customer_id', $this->getCustomerId())
                    ->getFirstItem()->getData('gift_id');
            }
            if($giftId){
                $this->_coreRegistry->register('gift_id', $giftId);
                return true;
            }
        }
        return false;
    }
}
