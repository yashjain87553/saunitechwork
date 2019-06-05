<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 25/04/2016
 * Time: 16:35
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab;

/**
 * Class Items
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab
 */
class Items extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magenest\GiftRegistry\Model\Status
     */
    protected $_status;

    
    const COMM_TEMPLATE = 'customer/listitems.phtml';

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory
     */
    protected $_itemFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\GiftRegistryFactory
     */
    protected $_giftRegistryFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;


    /**
     * Items constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory
     * @param \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory $itemFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory $itemFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_customerFactory =$customerFactory;
        $this->_registrantFactory = $registrantFactory;
        $this->_giftRegistryFactory = $giftRegistryFactory;
        $this->_itemFactory = $itemFactory;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->_prepareForm();
        $this->setTemplate(static::COMM_TEMPLATE);
        return $this;
    }

    /**
     * params RegistrantId
     *
     * @return array list Items Gift Registry
     */
    public function getListItems()
    {
        $registrantId = $this->getRequest()->getParam('registrant_id');
        $connectRegistrant = $this->_registrantFactory->create()->load($registrantId);
        $connectGiftRegistry = $this->_giftRegistryFactory->create()->load($connectRegistrant->getGiftregistryId());
        $itemsCollection = $this->_itemFactory->create()
            ->addFieldToFilter('gift_id', $connectGiftRegistry->getGiftId())->getData();

        return $itemsCollection;
    }


    /**
     * @param $productId
     * @return $this
     */
    public function getInfoProduct($productId)
    {
        $data = $this->_productFactory->create()->load($productId);
        return $data;
    }


    /**
     * @param $idProduct
     * @return string
     */
    public function getViewItem($idProduct)
    {
        $data = $this->getUrl('catalog/product/edit', ['id' => $idProduct]);
        return $data;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Items Registry');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Items Registry');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param  string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
