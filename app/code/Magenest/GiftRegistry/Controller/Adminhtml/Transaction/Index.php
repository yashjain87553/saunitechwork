<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 22:48
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Transaction;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Class Index
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Transaction
 */
class Index extends Action
{

    public function execute()
    {
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magenest_GiftRegistry::giftregistry_order');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Order'));
        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }
}
