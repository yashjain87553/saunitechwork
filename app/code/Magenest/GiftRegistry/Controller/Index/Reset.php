<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 8/5/17
 * Time: 4:14 PM
 */

namespace Magenest\GiftRegistry\Controller\Index;

class Reset extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory
     */
    protected $_eventFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $eventFactory,
        \Magento\Framework\App\Action\Context $context
    ) {
    
        $this->_logger = $logger;
        $this->_eventFactory = $eventFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $registry = $this->_eventFactory->create()->load($params['event_id']);
        $registry->setImage('');
        $registry->save();
        $this->messageManager->addSuccess('The image has reset successfully!');
        return $this->resultRedirectFactory->create()->setPath('giftregistrys/index/manageregistry/', ['type'=>$params['type'],'event_id'=> $params['event_id']]);
    }
}
