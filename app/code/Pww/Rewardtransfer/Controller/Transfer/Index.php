<?php
namespace Pww\Rewardtransfer\Controller\Transfer;
 
class Index extends \Magento\Framework\App\Action\Action
{
        /**
         * @var \Magento\Framework\View\Result\PageFactory
         */
        protected $resultPageFactory;
        protected $_customerSession;

 
        /**
         * @param \Magento\Framework\App\Action\Context $context
         * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
         */
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Customer\Model\SessionFactory $customerSession,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory
        )
        {
            $this->resultPageFactory = $resultPageFactory;
             $this->_customerSession = $customerSession->create();
            parent::__construct($context);
        }
    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_customerSession->isLoggedIn()) {
           $this->messageManager->addError(__("Please Login First ,To Access Reward Transfer Functionality"));
               return $resultRedirect->setPath('customer/account/login/');
        }
         
        return $this->resultPageFactory->create();
    }
}
?>