<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 23:02
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Registry;

/**
 * Class MassStatus
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Registry
 */
class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * Update blog post(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $testIds = $this->getRequest()->getParam('registry');
        if (!is_array($testIds) || empty($testIds)) {
            $this->messageManager->addError(__('Please select product(s).'));
        } else {
            try {
                $status = (int) $this->getRequest()->getParam('active');
                foreach ($testIds as $testId) {
                    $post = $this->_objectManager->get('Magenest\GiftRegistry\Model\Event')->load($testId);
                    $post->setStatus($status)->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($testId))
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
