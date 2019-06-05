<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 20/04/2016
 * Time: 10:39
 */

namespace Magenest\GiftRegistry\Controller\Customer;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Item
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class Item extends Action
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
     * @var \Magenest\GiftRegistry\Model\AddressFactory
     */
    protected $addressFactory;

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
     * @var \Magenest\GiftRegistry\Model\ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\Item\OptionFactory
     */
    protected $_optionFactory;

    protected $_logger;

    /**
     * Item constructor.
     * @param GiftRegistryFactory $registryFactory
     * @param \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory
     * @param \Magenest\GiftRegistry\Model\AddressFactory $addressFactory
     * @param \Magenest\GiftRegistry\Model\Item\OptionFactory $optionFactory
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param ItemFactory $itemFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        GiftRegistryFactory $registryFactory,
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        \Magenest\GiftRegistry\Model\AddressFactory $addressFactory,
        \Magenest\GiftRegistry\Model\Item\OptionFactory $optionFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magenest\GiftRegistry\Model\ItemFactory $itemFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_context = $context;
        $this->_currentCustomer = $currentCustomer;
        $this->_customerSession = $session;
        $this->registryFactory = $registryFactory;
        $this->registrantFactory = $registrantFactory;
        $this->addressFactory = $addressFactory;
        $this->_optionFactory = $optionFactory;
        $this->_itemFactory = $itemFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            $this->messageManager->addWarning(__('Please login to continue.'));
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('customer/account/login');
            return $resultForward;
        }

        /**
        * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
        */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $id = $this->getRequest()->getParam('id');
        $type = $this->getRequest()->getParam('type');
        if ($type== 'massdelete') {
            $this->delete();
        } else {
            $this->save();
        }
        $giftID = $this->getRequest()->getParam('event_id');
        $giftType = $this->getRequest()->getParam('event_type');
        $resultRedirect->setPath('giftregistrys/index/manageregistry/', ['type'=>$giftType , 'event_id' => $giftID]);
        if ($type!== 'massdelete') {
            return $resultRedirect;
        }
    }
    
    public function delete()
    {
        $itemIds=$this->getRequest()->getParam('listdelete');
        $qtyItem = 0;
        foreach ($itemIds as $itemId) {
            if ($itemId) {
                try {
                    $item = $this->_itemFactory->create()->load($itemId);
                    $item->delete();
                    //delete item option
                    $options = $this->_optionFactory->create()->getCollection()->addFieldToFilter('gift_item_id', $itemId);
                    foreach ($options as $option) {
                        $option->delete();
                    }
                    $qtyItem++;
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('There is error while deleting item(s)'));
                }
            }
        }
        $this->messageManager->addSuccess(__('You has been deleted '.$qtyItem.' items from the registry successfully!'));
    }
    public function save()
    {
//        $customer_id =  $this->_currentCustomer->getCustomerId();
//        $id = $this->getRequest()->getParam('id');
        $items = $this->getRequest()->getParam('item');
        try {
            if ($items) {
                foreach ($items as $key => $value) {
                    //key is item id
                    //value is $value['note'], $value['qty', $value['priority']

                    $item = $this->_itemFactory->create()->load($key);
                    $item->addData($value)->save();
                }
            }
            $this->messageManager->addSuccess(__('You updated the gift registry items successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('There is error while updating item(s)'));
        }
    }
}
