<?php
/**
 * Created by PhpStorm.
 * User: thienmagenest
 * Date: 17/07/2017
 * Time: 13:37
 */

namespace Magenest\GiftRegistry\Controller\Guest;

class SearchTypeGift extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory
     */
    protected $_eventFactory;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $_context;

    /**
     * ListSearch constructor.
     * @param \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $eventFactory
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $eventFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {

        $this->_context = $context;
        $this->_eventFactory = $eventFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
