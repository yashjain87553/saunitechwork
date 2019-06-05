<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 8/5/17
 * Time: 12:46 PM
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * Class Upload
 * @package Magenest\GiftRegistry\Controller\Index
 */
class Upload extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory
     */
    protected $_eventFactory;

    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\Theme\Image
     */
    protected $imageModel;

    /**
     * Upload constructor.
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param UploaderFactory $fileUploaderFactory
     * @param \Magenest\GiftRegistry\Model\Theme\Image $imageModel
     * @param \Magenest\GiftRegistry\Model\GiftRegistryFactory $eventFactory
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        UploaderFactory $fileUploaderFactory,
        \Magenest\GiftRegistry\Model\Theme\Image $imageModel,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $eventFactory,
        \Magento\Framework\App\Action\Context $context
    ) {
    
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->imageModel = $imageModel;
        $this->_logger = $logger;
        $this->_eventFactory = $eventFactory;
        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getPostValue();
        $file = $this->getRequest()->getFiles('image');
        if (empty($file)) {
            $this->messageManager->addError('Something went wrong while upload the image!');
            return $this->resultRedirectFactory->create()->setPath('giftregistrys/index/manageregistry/', ['type'=>$params['registry_type'],'event_id'=> $params['registry_id']]);
        }
        $this->_logger->debug(print_r($file, true));
        $this->_logger->debug(print_r($params, true));
        $image = $this->uploadFileAndGetName('image', $this->imageModel->getBaseDir(), $file);
        $registry = $this->_eventFactory->create()->load($params['registry_id']);
        $registry->setImage($image);
        $registry->save();
        $this->messageManager->addSuccess('The image upload successfully!');
        return $this->resultRedirectFactory->create()->setPath('giftregistrys/index/manageregistry/', ['type'=>$params['registry_type'],'event_id'=> $params['registry_id']]);
    }

    /**
     * @param $input
     * @param $destinationFolder
     * @param $data
     * @return string
     * @throws \Exception
     */
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
}
