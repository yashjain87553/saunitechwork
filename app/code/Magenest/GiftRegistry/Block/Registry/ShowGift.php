<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 13/07/2017
 * Time: 17:35
 */

namespace Magenest\GiftRegistry\Block\Registry;

/**
 * Class ShowGift
 * @package Magenest\GiftRegistry\Block\Registry
 */
class ShowGift extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $_currentCustomer;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * BabyGift constructor.
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Directory\Block\Data $directoryBlock
     * @param \Magento\Framework\View\Element\Template\Context $context
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
    
        $this->_coreRegistry = $registry;
        $this->_currentCustomer = $currentCustomer;
        $this->_session = $session;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getStartedUrl()
    {
        $customerId = $this->_currentCustomer->getCustomerId();
        if (isset($customerId)!=0) {
//            if ($this->getGiftId()) {
//                return $this->getUrl('giftregistry').'manage'.str_replace('gift', '', $this->_coreRegistry->registry('type')).'.html';
//            }
            return $this->getUrl('giftregistry').'new'.str_replace('gift', '', $this->_coreRegistry->registry('type')).'.html';
        }
        return $this->getUrl('customer/account/login');
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        $customerId = $this->_currentCustomer->getCustomerId();
        return (isset($customerId)!=0) ? true : false;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/index');
    }

    /**
     * @return string
     */
    public function getNewUrl()
    {
        if ($this->isCustomerLoggedIn()) {
            return $this->getUrl('giftregistry').'new'.str_replace('gift', '', $this->_coreRegistry->registry('type')).'.html';
        } else {
            return $this->getUrl('customer/account/login/');
        }
    }

    public function getLoginUrl()
    {
        return $this->getUrl('customer/account/login/');
    }

    public function getEventType()
    {
        return $this->_coreRegistry->registry('type');
    }

    public function getGiftId()
    {
        return $this->_coreRegistry->registry('gift_id') ? $this->_coreRegistry->registry('gift_id') : false;
    }
    public function getUrlAction()
    {
        return $this->getUrl('giftregistry')."searchtype.html";
    }
}
