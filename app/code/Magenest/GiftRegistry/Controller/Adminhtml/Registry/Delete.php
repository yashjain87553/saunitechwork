<?php
/**
 * Created by PhpStorm.
 * User: canhnd
 * Date: 22/06/2017
 * Time: 15:40
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Registry;

use Magenest\GiftRegistry\Controller\Adminhtml\GiftRegistry;
use Magenest\GiftRegistry\Model\Item\OptionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class Delete
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Registry
 */
class Delete extends GiftRegistry
{

    protected $_optionFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        GiftRegistryFactory $_giftregistryFactory,
        RegistrantFactory $_registranFactory,
        ItemFactory $_itemFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        Filter $filter,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface,
        OptionFactory $optionFactory
    ) {
    
        parent::__construct($context, $coreRegistry, $resultPageFactory, $_giftregistryFactory, $_registranFactory, $_itemFactory, $resultRawFactory, $layoutFactory, $filter, $urlRewrite, $resultForwardFactory, $productFactory, $categoryFactory, $configInterface);
        $this->_optionFactory = $optionFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $registryIds = $this->getRequest()->getParam('registrant_id');
        
        if (empty($registryIds)) {
            $this->messageManager->addError(__('Please select product(s).'));
        } else {
            try {
                $this->deleteRegistry($registryIds);
                $this->messageManager->addSuccess(__('A total of 1 record have been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
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
