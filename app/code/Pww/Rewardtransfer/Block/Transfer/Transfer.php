<?php
namespace Pww\Rewardtransfer\Block\Transfer;
 
class Transfer extends \Magento\Framework\View\Element\Template
{
	protected $_customerSession;
    protected $rewardsBalance;

    public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        array $data = []
    ) {
       $this->rewardsBalance = $rewardsBalance;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }


    public function getBalancepoint()
    {
       if ($this->_customerSession->isLoggedIn()) {
            $amountBalace = $this->rewardsBalance->getBalancePoints($this->_customerSession);
            return $amountBalace;
        }
        else {
            return  "Not Login";
        }

       
    }
}