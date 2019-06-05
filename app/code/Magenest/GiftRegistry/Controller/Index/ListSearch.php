<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 7/15/17
 * Time: 3:42 PM
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class ListSearch
 * @package Magenest\GiftRegistry\Controller\Index
 */
class ListSearch extends \Magento\Framework\App\Action\Action
{

    protected $_registryFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\Registrant\CollectionFactory
     */
    protected $_registrantFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    protected $_logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $registryFactory,
        ResultJsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
    
        $this->_logger = $logger;
        $this->_registrantFactory = $registrantFactory;
        $this->_registryFactory = $registryFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_coreRegistry = $registry;
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage =  $this->_pageFactory->create();
        return $resultPage;
    }
}
