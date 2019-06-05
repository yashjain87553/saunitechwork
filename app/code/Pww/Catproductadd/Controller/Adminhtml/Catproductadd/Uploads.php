<?php

namespace Pww\Catproductadd\Controller\Adminhtml\Catproductadd;

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
    protected $_CategoryLinkRepository;
    protected $_CategoryLinkManagementInterface;
    protected $_productCollectionFactory;
    protected $_product;

    /**
     * constructor
     * 
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Catalog\Model\Product $product,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\CategoryLinkRepository $CategoryLinkRepository,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $CategoryLinkManagementInterface ,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {   
        $this->uploaderFactory = $uploaderFactory;
        $this->_product = $product;
        $this->resultPageFactory = $resultPageFactory;
        $this->fileSystem = $fileSystem;
        $this->_CategoryLinkRepository = $CategoryLinkRepository;
        $this->_CategoryLinkManagementInterface = $CategoryLinkManagementInterface;
        $this->_productCollectionFactory = $productCollectionFactory;
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
        $destinationFolder = $this->fileSystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath("/categoryproductsfile");
        $catid=$data['file_type'];
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
                $categories = [$catid];//category ids array
                $collection = $this->_productCollectionFactory->create();
                $collection->addAttributeToSelect('sku');
                $collection->addCategoriesFilter(['in' => $categories]);
                $pro_collection=$collection->getData();
                $categoryId = $catid;
                $filesku=[];
                while(! feof($file)){
                          $arr=fgetcsv($file);
                          $option=$arr[0];
                    if($option == '') {
                        continue;
                    }
                    else{
                        if(!in_array($option, $filesku)) {
                        array_push($filesku,$option);
                    }
                    }

                }
                foreach($pro_collection as $pro)
                {
                    $sku = $pro['sku'];
                    if(!in_array($sku, $filesku)) {
                    $this->_CategoryLinkRepository->deleteByIds($categoryId,$sku);
                  }else{
                    if (($key = array_search($sku, $filesku)) !== false) {
                        unset($filesku[$key]);
                    }
                  }
                }    
                $category_ids = array($catid);
                $invalidsku=[];
                  foreach($filesku as $sku2){
                    $categories=NULL;
                    $product=NULL;
                    $id=NULL;
                    if($this->_product->getIdBySku($sku2)) {
                         $id=$this->_product->getIdBySku($sku2);
                        $product = $this->_product->load($id);
                        $categories = $product->getCategoryIds();
                        $categories[]=$catid;
                        
                        $this->_CategoryLinkManagementInterface->assignProductToCategories($sku2, $categories);
                    }
                    else{
                     array_push($invalidsku,$sku2);
                    }
                  }
                fclose($file);
                if(count($invalidsku)>0)
                {
                  $strreturn='Records uploaded successfully and invalid skus are : ';
                  foreach($invalidsku as $invalid)
                  {
                    $strreturn .=$invalid." , ";
                  }
                  $this->messageManager->addSuccess(__($strreturn));
                }
                else{
                  $this->messageManager->addSuccess(__('Records uploaded successfully'));
                }
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
