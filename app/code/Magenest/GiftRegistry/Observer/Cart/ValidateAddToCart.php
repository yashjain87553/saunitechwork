<?php

namespace Magenest\GiftRegistry\Observer\Cart;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ValidateAddToCart implements ObserverInterface
{

    protected $data;
    protected $_session;

    public function __construct(
        \Magenest\GiftRegistry\Helper\Data $data,
        \Magento\Checkout\Model\Session $session
    )
    {
        $this->data = $data;
        $this->_session = $session;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $this->_session->getQuote();
        if(!$quote->getIsValidateExpired()){
            $quote->setData('is_validate_expired', true);
            $items = $quote->getAllItems();
            $isExpired = $this->data->isQuoteContainExpiredEventItem($items);
            if ($isExpired) {
                throw new \Magento\Framework\Exception\LocalizedException(__("Event has expired, please clear your cart before place an another order."));
            }
        }
    }
}