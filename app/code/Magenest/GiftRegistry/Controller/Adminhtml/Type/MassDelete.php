<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 23:02
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Type;

/**
 * Class MassDelete
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Type
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $types = $this->getRequest()->getParam('type');
        
        if (!is_array($types) || empty($types)) {
            $this->messageManager->addError(__('Please select type(s).'));
        } else {
            try {
                for ($i = 0; $i < count($types); $i++) {
                    $post = $this->_objectManager->get('Magenest\GiftRegistry\Model\Type')->load($types[$i]);
                    $post->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($types))
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
