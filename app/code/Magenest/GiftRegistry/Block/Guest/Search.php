<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 01/12/2015
 * Time: 14:10
 */
namespace Magenest\GiftRegistry\Block\Guest;

/**
 * Class Search
 * @package Magenest\GiftRegistry\Block\Guest
 */
class Search extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory
     */
    protected $_eventFactory;

    /**
     * Search constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $eventFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $eventFactory,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->currentCustomer = $currentCustomer;
        $this->_eventFactory = $eventFactory;
    }

    /**
     * @return string
     */
    public function getIdEvent()
    {
        $info1 = $this->getRequest()->getParam('event_fn');
        $info2 = $this->getRequest()->getParam('event_ln');
        if ($info1 != null && $info2 != null) {
            $urlLink = $this->getUrl('giftregistrys/customer/registry/', ['ship_firstname' => $info1], ['ship_lastname' => $info2]);
            return $urlLink;
        } else {
            $urlLink = 'giftregistrys/customer/registry';
            return $urlLink;
        }
    }

    /**
     * @return mixed
     */
    public function getBaseUrlEvent()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
