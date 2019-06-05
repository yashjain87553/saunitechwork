<?php
/**
 * Created by PhpStorm.
 * User: ninhvu
 * Date: 17/08/2018
 * Time: 09:33
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class GiftregistryCustomer extends Action
{


    protected $customerSession;


    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->giftRegistry = $giftRegistryFactory;

    }


    public function execute()
    {
       $customerId = $this->customerSession->getId();
       $giftRegistrys = $this->giftRegistry->create()->getCollection()
           ->addFieldToFilter("customer_id",$customerId)
           ->addFieldToFilter('is_expired',0);
       $data = [];
       $i=1;
       foreach ($giftRegistrys as $giftRegistry){
           $data[$giftRegistry->getGiftId()] = $giftRegistry->getType();
           $i++;
       }
       $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
       $resultJson->setData($data);
       return $resultJson;
    }

}