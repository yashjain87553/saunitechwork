<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 11/07/2017
 * Time: 15:31
 */
namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class NewRegistry
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class NewGift extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var GiftRegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\RegistrantFactory
     */
    protected $registrantFactory;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $_context;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $_currentCustomer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * NewRegistry constructor.
     * @param GiftRegistryFactory $registryFactory
     * @param \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        GiftRegistryFactory $registryFactory,
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {

        $this->_context = $context;
        $this->_currentCustomer = $currentCustomer;
        $this->_customerSession = $session;
        $this->registryFactory = $registryFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->registrantFactory = $registrantFactory;

        parent::__construct($context);
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            $this->messageManager->addWarning(__('Please login to continue.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('customer/account/login');
        }
//        if ($this->checkCreated($this->getRequest()->getParam('type'))) {
//            $this->messageManager->addWarning(__('You have created this event! Please check!'));
//            $resultRedirect = $this->resultRedirectFactory->create();
//            return $resultRedirect->setPath('giftregistrys/customer/mygiftregistry');
//        }
        $resultPage = $this->resultPageFactory->create();
        $giftRegistryType = $this->getRequest()->getParam('type');
        $this->coreRegistry->register('type', $giftRegistryType);
        $resultPage->getConfig()->getTitle()->set(__('Add New GiftRegistry'));
        return $resultPage;
    }

    public function checkCreated($type)
    {
        $registry = $this->registryFactory->create()->getCollection()->addFieldToFilter('customer_id', $this->_customerSession->getCustomerId())->addFieldToFilter('type', $type);
        if ($registry->count()) {
            return true;
        }
        return false;
    }
}
