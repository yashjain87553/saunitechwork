<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 01/12/2015
 * Time: 14:10
 */

namespace Magenest\GiftRegistry\Block\Customer\Registry;

/**
 * Class NewRegistry
 * @package Magenest\GiftRegistry\Block\Customer\Registry
 */
class NewRegistry extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory
     */
    protected $_registryFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory
     */
    protected $_typeFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\GiftRegistryFactory
     */
    protected $_giftRegistryFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magenest\GiftRegistry\Model\RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * NewRegistry constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $eventFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory $typeFactory
     * @param \Magenest\GiftRegistry\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory
     * @param \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $eventFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory $typeFactory,
        \Magenest\GiftRegistry\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\CustomerFactory  $customerFactory,
        \Magento\Framework\Registry $registry,
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currentCustomer = $currentCustomer;
        $this->_registryFactory = $eventFactory;
        $this->_typeFactory = $typeFactory;
        $this->addressFactory = $addressFactory;

        $this->_giftRegistryFactory = $giftRegistryFactory;

        $this->_customerFactory = $customerFactory;

        $this->_coreRegistry = $registry;
        $this->_registrantFactory = $registrantFactory;
    }

    /**
     * @return int|null
     */
    public function getOwnerId()
    {
        $currentCustomerId = $this->currentCustomer->getCustomerId();
        return $currentCustomerId;
    }

    /**
     * @return \Magenest\GiftRegistry\Model\ResourceModel\Type\Collection
     */
    public function getActiveEventType()
    {
        /**
         * @var  $eventTypeCollection \Magenest\GiftRegistry\Model\ResourceModel\Type\Collection
         */
        $eventTypeCollection = $this->_typeFactory->create()->getActiveEventType();

        return $eventTypeCollection;
    }

    /**
     * @return mixed
     */
    public function getShippingAddress()
    {
        return $this->addressFactory->create()->getCollection();
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Add New Gift'));
        return parent::_prepareLayout();
    }


    /**
     * get the customer address
     *
     * @return array
     */
    public function getCustomerAddress()
    {
        $addressArr =[];
        $customerId =  $this->currentCustomer->getCustomerId();//->getAddressesCollection();

        $customer = $this->_customerFactory->create()->load($customerId);

        $addressCollection = $customer->getAddressesCollection();

        if ($addressCollection->getSize()) {

            /**
             * @var  $address \Magento\Customer\Model\Address
             */
            foreach ($addressCollection as $address) {
                $addressArr[] = ['name'=>$address->getName() . ' ' .$address->getStreetFull() . ' ' . $address->getRegion() . ' '. $address->getCountry(),
                    'id'=>$address->getId()
                ];
            }
        }

        return $addressArr;
    }

    /**
     * @return mixed
     */
    public function getGiftRegistry()
    {
        $registryId = $this->getRequest()->getParam('event_id');

        if (!empty($registryId)) {
            $data = $this->_giftRegistryFactory->create()
                ->getCollection()
                ->addFieldToFilter('gift_id', $registryId)->getFirstItem();

            if (!is_object($data)) {
                $data =  $this->_giftRegistryFactory->create();
            }
            return $data;
        } else {
            return $this->_giftRegistryFactory->create();
        }
    }

    /**
     * @return mixed
     */
    public function getRegistrants()
    {
        $registryId =  $this->_coreRegistry->registry('id');
        if ($registryId > 0) {
            $registrants=   $this->_registrantFactory->create()->getCollection()->addFieldToFilter('giftregistry_id', $registryId);
        } else {
            $registrants = $this->_registrantFactory->create()->getCollection()->addFieldToFilter('giftregistry_id', -1);
        }
        return $registrants;
    }

    /**
     * @return mixed
     */
    public function getRegistryType()
    {
        return $this->_coreRegistry->registry('type');
    }

    /**
     * @param $event
     * @return string
     */
    public function changePassword($event)
    {
        return $this->getUrl('giftregistrys/customer/changepass', ['event_id' => $event]);
    }
}
