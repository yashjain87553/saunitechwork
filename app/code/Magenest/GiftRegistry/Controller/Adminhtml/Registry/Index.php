<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 23:46
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Registry;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Class Index
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Registry
 */
class Index extends Action
{

    public function execute()
    {
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magenest_GiftRegistry::giftregistry_manager');
        $resultPage->getConfig()->getTitle()->prepend(__('Manager Gift Registry'));
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
