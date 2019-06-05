<?php
 
namespace Pww\Rewardtransfer\Controller\Transfer;
 
use Magento\Framework\App\Action\Context;
 
class Transfer extends \Magento\Framework\App\Action\Action
{

    protected $_resultPageFactory;
    protected $resultJsonFactory;
    protected $_customer;
    protected $_storemanager;
    protected $_customerSession;
    protected $rewardsBalance;
    protected $_customermain;
 
    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,\Magento\Customer\Model\CustomerFactory $customer, \Magento\Store\Model\StoreManagerInterface $storemanager,\Magento\Customer\Model\SessionFactory $customerSession,\Mirasvit\Rewards\Helper\Balance $rewardsBalance,\Magento\Customer\Model\Customer $customermain)
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customer = $customer;
        $this->_storemanager = $storemanager;
        $this->rewardsBalance = $rewardsBalance;
         $this->_customerSession = $customerSession->create();
         $this->_customermain = $customermain;
        parent::__construct($context);
    }
 
    public function execute()
    { 
       if (!$this->_customerSession->isLoggedIn()) {
           $this->messageManager->addError(__("Please Login First ,To Access Reward Transfer Functionality"));
               return $resultRedirect->setPath('customer/account/login/');
        }
        $ct_email = $this->getRequest()->getParam('ct_email');
        $points = $this->getRequest()->getParam('points');
        $websiteID = $this->_storemanager->getStore()->getWebsiteId();
        $customer = $this->_customer->create()->setWebsiteId($websiteID)->loadByEmail($ct_email);
        if ($customer->getId()==NULL){
        $mata['status']=false;
        $mata['message']=$ct_email." is not registered customer in our store .";
        $result = $this->resultJsonFactory->create();
        return $result->setData($mata);   
        }

        $deductpoint=-(int)$points;
        $message_deduct=$points." points is transfered to ".$ct_email." .";
        $addpoint=(int)$points;
        $emailMessage="";
        $customermain = $this->_customermain->getCollection()->addAttributeToFilter('entity_id', array('eq' => $this->_customerSession->getId()));
        $data=$customermain->getData();
        $message_add=$points." points is transfered by ".$data[0]['email']." .";

        if($ct_email==$data[0]['email']){

        $mata['status']=false;
        $mata['message']="Nothing To Do .";
        $result = $this->resultJsonFactory->create();
        return $result->setData($mata);
        }

        $amountBalace = $this->rewardsBalance->getBalancePoints($this->_customerSession);
        if($amountBalace>=$points){
        $this->rewardsBalance->changePointsBalance(
                    $this->_customerSession->getId(),
                    $deductpoint,  $message_deduct,
                    false,
                    false,
                    true,
                    $emailMessage
                );
        $this->rewardsBalance->changePointsBalance(
                    $customer->getId(),
                    $addpoint,  $message_add,
                    false,
                    false,
                    true,
                    $emailMessage
                );
    }
        $amountBalace = $this->rewardsBalance->getBalancePoints($this->_customerSession);
        $mata['status']=true;
        $mata['message']="Transfered Successfully";
        $mata['balance']=$amountBalace;
        $result = $this->resultJsonFactory->create();
        return $result->setData($mata);
    }
}
