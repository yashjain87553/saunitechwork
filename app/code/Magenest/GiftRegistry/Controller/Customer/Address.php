<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 29/03/2016
 * Time: 09:49
 */

namespace Magenest\GiftRegistry\Controller\Customer;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Address
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class Address extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * Address constructor.
     * @param Logger $logger
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $session
     */
    public function __construct(
        Logger $logger,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session
    ) {

        $this->_logger = $logger;
        $this->_customerSession = $session;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $customer = $this->_customerSession->getCustomer();
        if (!$customer->getId()) {
            return;
        }
        $addresses = $customer->getAddresses();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $responseData=[];

        if (count($addresses) > 0) {
            foreach ($addresses as $address) {
                $adressId = $address->getData('entity_id');
                $adressLabel = $address->getData('firstname') . ' ' .  $address->getData('lastname') . ' '. $address->getData('street');
                $responseData[]=[
                'id' =>$adressId,
                'label'=>$adressLabel
                ];
            }
        }


        $resultJson->setData($responseData);
        return  $resultJson;
    }
}
