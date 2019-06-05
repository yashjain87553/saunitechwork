<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 13/03/2016
 * Time: 00:36
 */

namespace Magenest\GiftRegistry\Controller\Customer;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class View
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * View constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Registry $registry
    ) {
        $this->coreRegistry = $registry;
        $this->_customerSession = $session;
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
        if (!$this->_customerSession->isLoggedIn()) {
            $this->messageManager->addWarning(__('Please login to continue.'));
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('customer/account/login');
            return $resultForward;
        }

        $id = $this->getRequest()->getParam('id');
        $this->coreRegistry->register('registry', $id);
        $items = $this->_objectManager->create('Magenest\GiftRegistry\Model\Item')->getCollection();
        $this->coreRegistry->register('items', $items);
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Gift Registry Items'));
        $this->_view->renderLayout();
    }
}
