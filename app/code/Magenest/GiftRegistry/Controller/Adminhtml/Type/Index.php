<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 22:48
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Type;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Class Index
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Type
 */
class Index extends Action
{

    public function execute()
    {
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->messageManager->addNotice("Please contact to producer (support@magenest.com) to customize new event type!");
        $resultPage->setActiveMenu('Magenest_GiftRegistry::giftregistry_event_type');
        $resultPage->getConfig()->getTitle()->prepend(__('Event Type'));
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
