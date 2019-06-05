<?php
/**
 * Created by PhpStorm.
 * User: duchai
 * Date: 22/01/2019
 * Time: 15:47
 */

namespace Magenest\GiftRegistry\Controller\Registry;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

class Delete extends Action
{

    protected $customerSession;

    protected $logger;

    protected $giftRegistryFactory;

    protected $giftRegistryOrderFactory;

    protected $registry = null;

    protected $registrantFactory;

    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Psr\Log\LoggerInterface $logger,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory,
        \Magenest\GiftRegistry\Model\TranFactory $tranFactory,
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        Context $context
    )
    {
        $this->giftRegistryOrderFactory = $tranFactory;
        $this->giftRegistryFactory = $giftRegistryFactory;
        $this->logger = $logger;
        $this->customerSession = $session;
        $this->registrantFactory = $registrantFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultRedirectFactory->create();
        $resultPage->setUrl($this->_redirect->getRefererUrl());
        try {
            $this->deleteRegistry();
            $this->messageManager->addSuccess(__("A registry have been deleted."));
        } catch (LocalizedException $exception) {
            $this->messageManager->addError($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addError(__("Can't delete Registry now."));
        }
        return $resultPage;
    }

    protected function getCustomerId()
    {
        $customerId = $this->customerSession->getId();
        if (!$customerId) {
            throw new LocalizedException(__('Please Login To Do This Action.'));
        }
        return $customerId;
    }

    protected function deleteRegistry()
    {
        $registry = $this->getRegistry();
//        $this->validateDeleteRegistry();
        //delete registrant of gift registry
        $this->deleteRegistrant($registry);
        $registry->delete();
    }

    protected function getRegistry()
    {
        if ($this->registry === null) {
            $registryId = $this->getRequest()->getParam('id');
            if (!$registryId || !is_numeric($registryId)) {
                throw new LocalizedException(__('Invalid Registry Id.'));
            }
            $registry = $this->giftRegistryFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $this->getCustomerId())
                ->addFieldToFilter('gift_id', $registryId)
                ->getFirstItem();
            if (!$registry || !$registry->getId()) {
                throw new LocalizedException(__('Registry Not Existed.'));
            }
            $this->registry = $registry;
        }
        return $this->registry;
    }

    protected function validateDeleteRegistry(){
        if($this->isEventTimePassed()){
            return;
        }
        $this->validateOrder();
    }

    protected function isEventTimePassed(){
        $registry = $this->getRegistry();
        $date = $registry->getDate();
        $today = date('Y-m-d');
        $today = date_create_from_format('Y-m-d H:i:s',"$today 00:00:00");
        $date = date_create_from_format('Y-m-d H:i:s',date_create_from_format('Y-m-d H:i:s',$date)->format('Y-m-d').' 00:00:00');
        $dateInterval = $today->diff($date);
        $dateDiff = $dateInterval->format('%a');
        if($dateDiff > 0){
            return true;
        }
        return false;
    }

    protected function validateOrder(){
        $registry = $this->getRegistry();
        $collection = $this->giftRegistryOrderFactory->create()->getCollection()
            ->addFieldToFilter('giftregistry_id',$registry->getId());
        if($collection->getSize() > 0){
            throw new LocalizedException(__("Can't delete registry with order placed."));
        }
    }

    protected function deleteRegistrant($registry)
    {
        $this->registrantFactory->create()->getCollection()
            ->addFieldToFilter('giftregistry_id', $registry->getId())
            ->walk('delete');
    }
}