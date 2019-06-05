<?php
/**
 * Created by PhpStorm.
 * User: canhnd
 * Date: 22/06/2017
 * Time: 15:40
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Type;

/**
 * Class Delete
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Type
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('id');

        if (empty($type)) {
            $this->messageManager->addError(__('Please select type(s).'));
        } else {
            try {
                $post = $this->_objectManager->get('Magenest\GiftRegistry\Model\Type')->load($type);
                $post->delete();
                $this->messageManager->addSuccess(
                    __('A total of 1 record have been deleted.')
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }
}
