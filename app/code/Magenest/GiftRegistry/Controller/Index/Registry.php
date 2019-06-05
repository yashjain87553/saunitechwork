<?php
/**
 * Created by Magenest.
 * User: trongpq
 * Date: 12/2/17
 * Time: 01:09
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;

/**
 * Class Registry
 * @package Magenest\GiftRegistry\Controller\Index
 */
class Registry extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * RegistryLogin constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        ResultJsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->_session = $session;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_coreRegistry = $registry;
        return parent::__construct($context);
    }

    public function execute()
    {
        $param = $this->getRequest()->getParams();
        $request = $param['request'];
        if ($request == 'registry_login') {
            $this->_session->setRegistryLogin(true);
            $this->_session->setRegistryType($param['type']);
        }
        return $this->resultJsonFactory->create()->setData(['registry' => 'success']);
        // TODO: Implement execute() method.
    }
}
