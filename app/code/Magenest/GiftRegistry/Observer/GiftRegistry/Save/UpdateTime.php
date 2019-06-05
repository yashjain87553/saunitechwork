<?php
/**
 * Created by PhpStorm.
 * User: duchai
 * Date: 27/12/2018
 * Time: 13:31
 */
namespace Magenest\GiftRegistry\Observer\GiftRegistry\Save;

use Magenest\GiftRegistry\Model\GiftRegistry;
use Magenest\GiftRegistry\Model\Registrant;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UpdateTime implements ObserverInterface{

    protected $registrantFactory;

    protected $dataHelper;

    public function __construct(
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        \Magenest\GiftRegistry\Helper\Data $dataHelper
    )
    {
        $this->registrantFactory = $registrantFactory;
        $this->dataHelper = $dataHelper;
    }

    public function execute(Observer $observer)
    {
        try {
            $giftRegistry = $observer->getEvent()->getGiftRegistry();
            if (!($giftRegistry instanceof GiftRegistry) || !$giftRegistry->getId()) {
                return;
            }
            if ($giftRegistry->getUpdatedAt() && $giftRegistry->getUpdatedAt() == $giftRegistry->getOrigData('updated_at')) {
                return;
            }
            $updatedTime = $giftRegistry->getUpdatedAt();
            $registrant = $this->getRegistrantModel($giftRegistry);
            $registrant->setUpdatedTime($updatedTime);
            $registrant->save();
        }catch (\Exception $exception){

        }
        // update gift expired or not depend date
        $this->dataHelper->updateExpiredGift();
    }

    /**
     * @param GiftRegistry $giftRegistry
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    private function getRegistrantModel(GiftRegistry $giftRegistry){
        $registrant = $this->registrantFactory->create()->getCollection()
            ->addFieldToFilter('giftregistry_id',$giftRegistry->getId())
            ->getFirstItem();
        if(!($registrant instanceof Registrant) || !$registrant->getId()){
            throw new \Exception(__("Registrant Not Exist"));
        }
        return $registrant;
    }
}