<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 16:15
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Type;

use \Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magenest\GiftRegistry\Model\TypeFactory;

/**
 * Class Save
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Type
 */
class Save extends \Magento\Backend\App\Action
{

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\Theme\Image
     */
    protected $imageModel;

    protected $typeFactory;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        UploaderFactory $fileUploaderFactory,
        \Magenest\GiftRegistry\Model\Theme\Image $imageModel,
        TypeFactory $typeFactory
    ) {
    
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_logger = $logger;
        $this->imageModel = $imageModel;
        $this->typeFactory = $typeFactory;
        parent::__construct($context);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $type = $data['event_type'];

        /**
         * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();

        $listEvent = $this->typeFactory->create()->getCollection()->getData();
        foreach ($listEvent as $event) {
            if ($event['event_type'] == $type) {
                if (isset($data['id']) && $data['id'] == $event['id']) {
                    continue;
                } else {
                    $this->messageManager->addError('This event code already exists, please try again!');
                    return $resultRedirect->setPath('*/*/');
                }
            }
        }

        if ($data) {
            $model = $this->_objectManager->create('Magenest\GiftRegistry\Model\Type');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                if ($id != $model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Wrong mapping rule.'));
                }
            }
            $model->setData($data);
            $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($model->getData());
            $imageName = $this->uploadFileAndGetName('image', $this->imageModel->getBaseDir(), $data);
            $model->setImage($imageName);
            try {
                $model->save();
                if ($id) {
                    $this->messageManager->addSuccess(__('The event type has been saved.'));
                }
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e, __('Something went wrong while saving the mapping.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

    public function uploadFileAndGetName($input, $destinationFolder, $data)
    {
        try {
            if (isset($data[$input]['delete'])) {
                return '';
            } else {
                $uploader = $this->_fileUploaderFactory->create(['fileId' => $input]);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                return $result['file'];
            }
        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                throw new \Exception($e->getMessage());
            } else {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }
}
