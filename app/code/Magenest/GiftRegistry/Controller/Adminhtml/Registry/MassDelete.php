<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 23:02
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Registry;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant\CollectionFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\Item\OptionFactory;

/**
 * Class MassDelete
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Registry
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var RegistrantFactory
     */
    protected $_registrantFactory;

    protected $_optionFactory;


    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory, ItemFactory $itemFactory, RegistrantFactory $registrantFactory, OptionFactory $optionFactory)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_itemFactory = $itemFactory;
        $this->_registrantFactory = $registrantFactory;
        $this->_optionFactory = $optionFactory;
        parent::__construct($context);
    }
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection as $item) {
            $registrant_id = $item->getRegistrantId();
            $this->deleteRegistry($registrant_id);
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * delete gift registry
     *
     * @param $id
     */
    public function deleteRegistry($id)
    {
        $registration = $this->_registrantFactory->create()->load($id)->getData();
        if ($registration['giftregistry_id']) {
            $registry = $this->_objectManager->get('Magenest\GiftRegistry\Model\GiftRegistry')->load($registration['giftregistry_id']);

            if ($registry) {
                $items = $this->_itemFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('gift_id', $registration['giftregistry_id'])
                    ->getData();
                foreach ($items as $item) {
                    $item = $this->_objectManager->get('Magenest\GiftRegistry\Model\Item')->load($item['gift_item_id']);
                    $options = $this->_optionFactory->create()->getCollection()->addFieldToFilter('gift_item_id', $item['gift_item_id']);
                    foreach ($options as $option) {
                        $option->delete();
                    }
                    $item->delete();
                }
            }

            $registry->delete();
        }

        $registration = $this->_objectManager->get('Magenest\GiftRegistry\Model\Registrant')->load($id);
        $registration->delete();

        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }
}
