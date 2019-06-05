<?php

namespace Pww\Filemanager\Controller\Adminhtml\Filemanager;

class Uploads extends \Magento\Backend\App\Action
{
    /**
     * Page result factory
     * 
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Page factory
     * 
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $uploaderFactory;
    protected $resultPage;
    protected $fileSystem;

    /**
     * constructor
     * 
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Eav\Model\Entity\AttributeFactory $eavAttributeFactory,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {   
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->uploaderFactory = $uploaderFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->fileSystem = $fileSystem;
        parent::__construct($context);
    }

    /**
     * execute the action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPost();
        if($_FILES['fileToUpload']['name']){
        $destinationFolder = $this->fileSystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath("/".$data['file_type']);
        $name=$_FILES['fileToUpload']['name'];
        $name = str_replace(' ', '_', $name);
        $ext = explode(".", $name);
        $filename = $destinationFolder.'/'.$name;
        if($ext[1]!='csv')
        {
            $this->messageManager->addError(__("Please upload csv format only"));
               return $resultRedirect->setPath('*/*/');
        }

        if (file_exists($filename)) {
            $filedate= date("M d Y H:i:s",filemtime($filename));
            $newname =$destinationFolder."/".$ext[0]."--".$filedate.".".$ext[1];
            rename($filename,$newname);
        } 
        try {
        $uploader = $this->uploaderFactory->create(['fileId' =>'fileToUpload']);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                $file = fopen($filename,"r");
                $magentoAttribute = $this->eavAttributeFactory->create()->loadByCode('catalog_product', $data['file_type']);
                $attributeCode = $magentoAttribute->getAttributeCode();
                $magentoAttributeOptions = $this->attributeOptionManagement->getItems(
                    'catalog_product',
                    $attributeCode
                );
                $existingMagentoAttributeOptions = [];
                $newOptions = [];
                $counter = 0;
                foreach($magentoAttributeOptions as $option) {
                    if (!$option->getValue()) {
                        continue;
                    }
                    if($option->getLabel() instanceof \Magento\Framework\Phrase) {
                        $label = $option->getText();
                    } else {
                        $label = $option->getLabel();
                    }

                    if($label == '') {
                        continue;
                    }

                    $existingMagentoAttributeOptions[] = $label;
                    $newOptions['value'][$option->getValue()] = [$label, $label];
                    $counter++;
                }
                $addedattribute=[];
                while(! feof($file)){
                          $arr=fgetcsv($file);
                          $option=$arr[0];
                    if($option == '') {
                        continue;
                    }

                    if(!in_array($option, $existingMagentoAttributeOptions) && !in_array($option, $addedattribute)) {
                        array_push($addedattribute,$option);
                        $newOptions['value']['option_'.$counter] = [$option, $option];
                    }

                    $counter++;
                }
                fclose($file);

                if(count($newOptions)) {
                    $magentoAttribute->setOption($newOptions)->save();
                }
                $this->messageManager->addSuccess(__('Records uploaded successfully'));
                return $resultRedirect->setPath('*/*/');
            }
        catch (\Exception $e) {
            if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                throw new \Magento\Framework\Exception\LocalizedException($e->getMessage());
                $this->messageManager->addError(__($e->getMessage()));
               return $resultRedirect->setPath('*/*/');
            }    
            $this->messageManager->addError(__("unable to upload this file"));
               return $resultRedirect->setPath('*/*/');     
    }
}else{
    $this->messageManager->addError(__("upload file can not be empty"));
               return $resultRedirect->setPath('*/*/');
}
}
   
   
}
