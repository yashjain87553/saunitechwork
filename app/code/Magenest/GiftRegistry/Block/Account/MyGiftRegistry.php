<?php

/**
 * Created by Magenest.
 * User: trongpq
 * Date: 4/23/18
 * Time: 14:25
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Block\Account;

use Magento\Framework\View\Element\Template;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;

/**
 * Class MyGiftRegistry
 * @package Magenest\GiftRegistry\Block\Account
 */
class MyGiftRegistry extends \Magento\Framework\View\Element\Template
{
    /**
     * @var GiftRegistryFactory
     */
    protected $giftregistryFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magenest\GiftRegistry\Model\TypeFactory
     */
    protected $_typeFactory;


    public function __construct(
        GiftRegistryFactory $giftRegistryFactory,
        \Magento\Customer\Model\Session $session,
        \Magenest\GiftRegistry\Model\TypeFactory $typeFactory,
        Template\Context $context,
        array $data = []
    ) {
    
        $this->giftregistryFactory = $giftRegistryFactory;
        $this->customerSession = $session;
        $this->_typeFactory = $typeFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getListRegistry()
    {
        $collection = $this->giftregistryFactory->create()->getCollection()->addFieldToFilter('customer_id', $this->customerSession->getCustomerId());
        return $collection->getData();
    }

    /**
     * @param $giftId
     * @param $type
     * @return string
     */
    public function getPreviewUrl($giftId, $type)
    {
        return $this->getUrl('giftregistry').'view'.str_replace('gift', '', $type).'.html?id='.$giftId;
    }

    /**
     * @param $type
     * @return string
     */
    public function getManageUrl($type, $giftId)
    {
        return $this->getUrl('giftregistrys/index/manageregistry',['type'=> $type, 'event_id' => $giftId]);
//        return $this->getUrl('giftregistry').'manage'.str_replace('gift', '', $type).'.html';
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * Get list type
     */
    public function getListEvent()
    {
        return $this->_typeFactory->create()->getCollection();
    }

    /**
     * @param $type
     * @return string
     * Get show-gift url via type of event.
     */
    public function getGiftUrl($type)
    {
        return $this->getUrl('giftregistry/')."new".str_replace('gift', '', $type).'.html';
    }

    public function getDeleteUrl($giftRegistryId){
        return $this->getUrl('giftregistrys/registry/delete',['id' => $giftRegistryId]);
    }
}
