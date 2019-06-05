<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 22/04/2016
 * Time: 08:33
 */

namespace Magenest\GiftRegistry\Helper;

/**
 * Class Cart
 * @package Magenest\GiftRegistry\Helper
 */
class Cart extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magenest\GiftRegistry\Model\GiftRegistryFactory
     */
    protected $_giftRegistryFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $_customerRepo;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $_quoteItemFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    protected $_optionFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializeHelper;

    protected $objectManager;

    protected $serialize;

    /**
     * Cart constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistry
     * @param \Magenest\GiftRegistry\Model\ItemFactory $item
     * @param \Magenest\GiftRegistry\Model\Item\OptionFactory $itemOptionGiftRegistryFactory
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $optionItemFactory
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistry,
        \Magenest\GiftRegistry\Model\ItemFactory $item,
        \Magenest\GiftRegistry\Model\Item\OptionFactory $itemOptionGiftRegistryFactory,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Quote\Model\Quote\Item\OptionFactory $optionItemFactory,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magenest\GiftRegistry\Model\Serializer $serialize
    ) {
        parent::__construct($context);
        $this->_customerSession = $session;
        $this->_checkoutSession = $checkoutSession;
        $this->_giftRegistryFactory = $giftRegistry;
        $this->_itemFactory = $item;
        $this->_customerRepo = $customerRepository;
        $this->_quote = $quote;
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->_addressFactory = $addressFactory;
        $this->_optionFactory = $optionItemFactory;
        $this->serialize = $serialize;
    }

    /**
     * @return bool
     */
    public function isForGiftRegistry()
    {

        $isForGiftRegistry = false;
        $quote = $this->_checkoutSession->getQuote();
        $items = $quote->getAllItems();

        if (count($items) > 0) {
            foreach ($items as $item) {
                $itemId = $item->getId();
                $option = $this->_optionFactory->create()->getCollection()->addFieldToFilter('item_id', $itemId)
                    ->addFieldToFilter('code', 'info_buyRequest')->getFirstItem();
                $buyRequestArray = null;
                if ($this->checkMagentoVersion()) {
                    //Magento2.2
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    //$this->serializeHelper = $objectManager->get("\\Magento\\Framework\\Serialize\\SerializerInterface");
                    $buyRequestArray = new \Magento\Framework\DataObject($this->serialize->unserialize($option->getData('value')));
                } else {
                    $buyRequestArray = $this->serialize->unserialize($option->getData('value'), true);
                }
                $buyRequest =$buyRequestArray;
                $registry = @$buyRequest['registry'];

                if ($registry) {
                    $isForGiftRegistry = true;
                }

                if ($isForGiftRegistry) {
                    break;
                }
            }
        }

        return $isForGiftRegistry;
    }

    /**
     * @return int
     */
    public function getNumberItemsInQuote()
    {
        $quote = $this->_checkoutSession->getQuote();
        $items = $quote->getAllItems();
        return count($items);
    }

    /**
     * @return int|mixed
     */
    public function getRegistryId()
    {
        $registry = 0;
//        $checkoutSession = $this->objectManager->create("Magento\Checkout\Model\Session");
        $quote = $this->_checkoutSession->getQuote();
        $items = $quote->getAllItems();
        if (count($items) > 0) {
            foreach ($items as $item) {
                $itemId = $item->getId();
                $option = $this->_optionFactory->create()->getCollection()->addFieldToFilter('item_id', $itemId)
                    ->addFieldToFilter('code', 'info_buyRequest')->getFirstItem();
                $buyRequestArray = null;
                if ($this->checkMagentoVersion()) {
                    //Magento2.2
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    //$this->serializeHelper = $objectManager->get("\Magento\Framework\Serialize\SerializerInterface");
                    $buyRequestArray = new \Magento\Framework\DataObject($this->serialize->unserialize($option->getData('value')));
                } else {
                    $buyRequestArray = $this->serialize->unserialize($option->getData('value'), true);
                }
                $buyRequest = $buyRequestArray;
                $registry = @$buyRequest['registry'];
                if ($registry) {
                    break;
                }
            }
        }
        return $registry;
    }

    /**
     * @return \Magento\Customer\Model\Address
     */
    public function getRegistryAddress()
    {
        $registry = 0;
        $customerAddress = $this->_addressFactory->create();
        $quote = $this->_checkoutSession->getQuote();
        $items = $quote->getAllItems();

        if (count($items) > 0) {
            foreach ($items as $item) {
                $itemId = $item->getId();

                $option = $this->_optionFactory->create()->getCollection()->addFieldToFilter('item_id', $itemId)
                    ->addFieldToFilter('code', 'info_buyRequest')->getFirstItem();
                $buyRequestArray = null;

                if ($this->checkMagentoVersion()) {
                    //Magento2.2
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    //$this->serializeHelper = $objectManager->get("\Magento\Framework\Serialize\SerializerInterface");
                    $buyRequestArray = new \Magento\Framework\DataObject($this->serialize->unserialize($option->getData('value')));
                } else {
                    $buyRequestArray = $this->serialize->unserialize($option->getData('value'), true);
                }

                $buyRequest = $buyRequestArray;
                $registry = @$buyRequest['registry'];
                if ($registry) {
                    break;
                }
            }
        }

        if ($registry) {
            $giftRegistry = $this->_giftRegistryFactory->create()->load($registry);
            $registryAdd = $giftRegistry->getData('shipping_address');
            $customerAddress->load($registryAdd);
        }

        return $customerAddress;
    }

    public function checkMagentoVersion()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $magentoVersion = $objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
        if (version_compare($magentoVersion, "2.2.0", ">=")) {
            return true;
        }
        return false;
    }
}
