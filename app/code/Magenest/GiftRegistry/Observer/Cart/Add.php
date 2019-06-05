<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 21/04/2016
 * Time: 14:14
 */
namespace Magenest\GiftRegistry\Observer\Cart;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class Add
 * @package Magenest\GiftRegistry\Observer\Cart
 */
class Add implements ObserverInterface
{
    /**
     * @var \Magenest\GiftRegistry\Helper\Cart
     */
    protected $_quoteHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * Add constructor.
     * @param \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistry
     * @param \Magenest\GiftRegistry\Model\ItemFactory $item
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magenest\GiftRegistry\Helper\Cart $cartHelper
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistry,
        \Magenest\GiftRegistry\Model\ItemFactory $item,
        \Magento\Quote\Model\Quote $quote,
        \Magenest\GiftRegistry\Helper\Cart $cartHelper,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_messageManager = $messageManager;
        $this->_customerRepository = $customerRepository;
        $this->_giftRegistryFactory = $giftRegistry;
        $this->_itemFactory = $item;
        $this->_logger = $logger;
        $this->_quote = $quote;
        $this->_quoteHelper = $cartHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $cartForRegistry =  $this->_quoteHelper ->isForGiftRegistry();
        $product = $observer->getEvent()->getProduct();
        $buyRequest = $observer->getEvent()->getBuyRequest();

        $itemsInQuote = $this->_quoteHelper->getNumberItemsInQuote();
        $registry = $buyRequest->getData('registry');
        $registryId = $this->_quoteHelper->getRegistryId();

            //there are case when there are item but no for any gift registry and there are item and for specific gift registry

        if ($cartForRegistry) {
            if ($registryId != $registry) {
                $this->_messageManager->addWarning("You must clear the gift for friend in the cart before buy your item!");
                throw new \Magento\Framework\Validator\Exception(__('You must clear the cart before buy gift for friend.'));
            }
        } else {
            if ($itemsInQuote > 1) {
                if ($registry) {
                    $this->_messageManager->addWarning("You must clear the cart before buy gift for your friend!");
                    throw new \Magento\Framework\Validator\Exception(__('You must clear the cart before buy gift for friend.'));
                }
            }
        }
    }
}
