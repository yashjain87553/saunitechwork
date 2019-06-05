<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 19/04/2016
 * Time: 16:21
 */

namespace Magenest\GiftRegistry\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\GiftRegistry\Model\RegistrantFactory;

/**
 * Class Item
 * @package Magenest\GiftRegistry\Observer\Order
 */
class Item implements ObserverInterface
{
    /**
     * @var \Magenest\GiftRegistry\Model\ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\GiftRegistryFactory
     */
    protected $_giftRegistryFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\TranFactory
     */
    protected $_orderRegistryFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $_customerRepository;

    /**
     * @var RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * Item constructor.
     * @param \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistry
     * @param \Magenest\GiftRegistry\Model\ItemFactory $item
     * @param \Magenest\GiftRegistry\Model\TranFactory $transactionFactory
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        RegistrantFactory $registrantFactory,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistry,
        \Magenest\GiftRegistry\Model\ItemFactory $item,
        \Magenest\GiftRegistry\Model\TranFactory $transactionFactory,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->_registrantFactory = $registrantFactory;
        $this->_customerRepository = $customerRepository;
        $this->_giftRegistryFactory = $giftRegistry;
        $this->_itemFactory = $item;
        $this->_orderRegistryFactory = $transactionFactory;
        $this->_logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $orderItem \Magento\Sales\Model\Order\Item
         */
        $orderItem = $observer->getEvent()->getItem();

        $status = $orderItem->getStatus();
        $order = $orderItem->getOrder();
        $orderId = $order->getId();

        $stateOfOrder = $order->getState();

        $product = $orderItem->getProduct();

        if($product){
            $productType = $product->getTypeId();

            $request = $orderItem->getProductOptionByCode('info_buyRequest');

            if (isset($request['registry']) && $request['registry']) {
                $registryId = $request['registry'];
                $item = $request['item'];
                $qty = $orderItem->getQtyOrdered();

                $this->_logger->debug($productType);
                /**
                 * @var  $order \Magento\Sales\Model\Order
                 */
                $order = $orderItem->getOrder();
                $registry = $this->_giftRegistryFactory->create()->load($registryId);

                //get the information of the receiver
                $registrant = $this->_registrantFactory->create()->getCollection()->addFieldToFilter('giftregistry_id', $registryId)->getFirstItem();
                $recipient_email = $registrant->getData('email');
                $recipient_name = $registrant->getData('firstname') . ' ' . $registrant->getData('lastname');
                $billingData = $order->getBillingAddress()->getData();
                $customerName = @$billingData['firstname'].' '.@$billingData['lastname'];
                $params = [
                    'giver_name' => $customerName,
                    'product_name' => $product->getName(),
                    'product_id' => $product->getId(),
                    'qty' => $qty,
                    'store_id' => $order->getStoreId(),
                    'recipient_email' => $recipient_email,
                    'recipient_name' => $recipient_name
                ];

                //save it in the gift registry order table for ease of managements
                $orderForGiftRegistryCollection = $this->_orderRegistryFactory->create()->getCollection()->addFieldToFilter('order_id', $orderId)
                    ->addFieldToFilter('giftregistry_id', $registryId);
                 if (!($orderForGiftRegistryCollection->getSize() > 0) && ($productType != 'configurable')) {
                    $orderForGiftRegistryData = [
                        'order_id' => $orderId,
                        'status' => $status,
                        'giftregistry_id' => $registryId
                    ];
                    $this->_orderRegistryFactory->create()->setData($orderForGiftRegistryData)->save();
                    $registry->sendEmail($order, $params);
                    //update the received quantity and send email to gift registry owner
                    $item = $this->_itemFactory->create()->load($item);
                    if($stateOfOrder == 'new') {
                        $receivedQty = $item->getData('received_qty');

                        $item->setData('received_qty', $receivedQty + $qty)->save();
                    }
                } else {
                    if ($productType != 'configurable') {
                        $orderItem = $this->_orderRegistryFactory->create()->getCollection()->addFieldToFilter('order_id', $orderId)
                            ->addFieldToFilter('giftregistry_id', $registryId)->getFirstItem();
                        $orderItem->addData(['status' => $stateOfOrder]);
                        $orderItem->save();
                        if($stateOfOrder=="new" && $status->getText() == "Ordered"){
                            $item = $this->_itemFactory->create()->load($item);
                            $receivedQty = $item->getData('received_qty');
                            $item->setData('received_qty', $receivedQty + $qty)->save();
                        }
                    }
                }
            }
        }

    }
}
