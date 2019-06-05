<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 13/07/2017
 * Time: 17:34
 */

namespace Magenest\GiftRegistry\Block\Registry;

/**
 * Class ListGift
 * @package Magenest\GiftRegistry\Block\Registry
 */
class ListGift extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $_currentCustomer;

    /**
     * @var \Magenest\GiftRegistry\Model\GiftRegistryFactory
     */
    protected $_registryFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\TypeFactory
     */
    protected $_typeFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * ListGift constructor.
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magenest\GiftRegistry\Model\TypeFactory $typeFactory
     * @param \Magento\Customer\Model\Session $session
     * @param \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     */
    public function __construct(
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magenest\GiftRegistry\Model\TypeFactory $typeFactory,
        \Magento\Customer\Model\Session $session,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->_productFactory = $productFactory;
        $this->_currentCustomer = $currentCustomer;
        $this->_registryFactory = $giftRegistryFactory;
        $this->_session = $session;
        $this->_typeFactory = $typeFactory;
        parent::__construct($context);
    }

    /**
     * @return list of registry via customer id
     */
    public function getRegistry()
    {
        $customerId = $this->_currentCustomer->getCustomerId();
        $registryCollection = $this->_registryFactory->create()->getCollection()->addFieldToFilter('customer_id', $customerId);
        return $registryCollection;
    }

    /**
     * @return title of page
     */
    public function getTitle()
    {
        return __('Welcome to Gift Registry');
    }

    /**
     * @return base url of store
     */
    public function getBaseUrlEvent()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function getListSearchUrl()
    {
        return $this->getUrl('giftregistry/search.html');
    }

    /**
     * @param $event
     * @return string
     */
    public function getViewUrl($event)
    {
        return $this->getUrl('giftregistry').'view'.str_replace('gift', '', $event['type']).'.html?id='.$event['gift_id'];
    }

    /**
     * @param $type
     * @return string
     * Get show-gift url via type of event.
     */
    public function getGiftUrl($type)
    {
        return $this->getUrl('giftregistry/').str_replace('gift', '', $type).'.html';
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * Get list type
     */
    public function getListEvent()
    {
        return $this->_typeFactory->create()->getCollection();
    }
}
