<?php

namespace Pww\Filemanager\Controller\Index;
 
use Magento\Framework\App\Action\Context;
 
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
 
    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
 
    public function execute()
    {    
        $this->messageManager->addSuccess(__('The file has been saved.')); 
        $this->_redirect("admin123/pwwfilemanager/filemanager"); 
        return;
    
    }
}