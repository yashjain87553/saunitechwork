<?php
 
namespace Pww\Homeproduct\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
 
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_resultJsonFactory;
    protected $_storeManager;
    protected $categoryRepository;
 
    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory,JsonFactory $resultJsonFactory,\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository)
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        $currentcatid = $this->getRequest()->getParam('currentcatid');
        if($currentcatid)
        {
        $category = $this->categoryRepository->get($currentcatid, $this->_storeManager->getStore()->getId());
        $caturl=$category->getUrl();
        if($currentcatid!=NULL){
        $block =  $resultPage->getLayout()->createBlock('Infortis\Base\Block\Product\ProductList\Featured')->setAttr(1)->setCategoryId($currentcatid)->setProductCount(8)->setCentered(1)->setPagination(2)->setHideButton(1)->setBlockName("Sale")->setTemplate('Magento_Catalog::product/list.phtml')->toHtml();
        }else{
            $block='';
            $caturl='';
        }
    }else{
          $this->_redirect('https://zirg.com');
            $block='';
            $caturl='';
        }
 
        $result->setData(['output' => $block,'caturl'=>$caturl]);
        return $result;
    }
}