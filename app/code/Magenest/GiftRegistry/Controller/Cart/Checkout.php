<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 22/04/2016
 * Time: 10:48
 */

namespace Magenest\GiftRegistry\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Checkout
 * @package Magenest\GiftRegistry\Controller\Cart
 */
class Checkout extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magenest\GiftRegistry\Helper\Cart
     */
    protected $_quoteHelper;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * Checkout constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magenest\GiftRegistry\Helper\Cart $cartHelper
     * @param \Magento\Quote\Model\Quote $cart
     */
    public function __construct(Context $context,\Magenest\GiftRegistry\Helper\Cart $cartHelper)
    {
        parent::__construct($context);
        $this->_quoteHelper = $cartHelper;
    }
//    public function __construct(
//        \Magento\Framework\App\Action\Context $context
//        \Magenest\GiftRegistry\Helper\Cart $cartHelper
//    ) {
//        parent::__construct($context);
//        $this->_quoteHelper = $cartHelper;
//    }

    public function execute()
    {
        $registryInCart = [
            'is_for_registry' => '',
            'registryId' => '',
            'registryAddress' => '',
            'registryAddressId' => ''
        ];


        $is_for_registry = $this->_quoteHelper->isForGiftRegistry();
        $registryId = $this->_quoteHelper->getRegistryId();
        /**
         * @var $customerAddress \Magento\Customer\Model\Address
         */
        $customerAddress = $this->_quoteHelper->getRegistryAddress();

        $registryAddress = [
            'firstname' => $customerAddress->getFirstname(),
            'lastname' => $customerAddress->getLastname(),
            'telephone' => $customerAddress->getTelephone(),
            'street' => $customerAddress->getStreet(),
            'postcode' => $customerAddress->getPostcode(),
            'city' => $customerAddress->getCity(),
            'region' => $customerAddress->getRegion(),
            'regionId' => (string)$customerAddress->getRegionId(),
            'regionCode' => $customerAddress->getRegionCode(),
            'country' => $customerAddress->getCountry(),
            'countryId' => $customerAddress->getCountryId(),
            'customerAddressId' => $customerAddress->getId(),
            'customerId' => $customerAddress->getCustomerId(),
        ];


        $registryInCart['is_for_registry'] = $is_for_registry;
        $registryInCart['registryId'] = $registryId;
        $registryInCart['registryAddressId'] = $customerAddress->getId();
        $registryInCart['registryAddress'] = $registryAddress;
        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($registryInCart);
        return $resultJson;
    }

}
