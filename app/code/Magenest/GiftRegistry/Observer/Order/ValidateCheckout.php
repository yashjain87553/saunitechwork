<?php

namespace Magenest\GiftRegistry\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ValidateCheckout implements ObserverInterface
{
    protected $data;
    public function __construct(
        \Magenest\GiftRegistry\Helper\Data $data

    )
    {
        $this->data = $data;
    }

    public function execute(Observer $observer)
    {
        $orderItems = $observer->getOrder()->getItems();
        $isExpired = $this->data->isQuoteContainExpiredEventItem($orderItems);
        if ($isExpired) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Event has expired, please clear your cart before place an another order."));
        }
    }


}