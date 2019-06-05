<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 23:45
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry;

/**
 * Class Grid
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magenest\GiftRegistry\Model\GiftRegistryFactory|\Magenest\GiftRegistry\Model\RegistrantFactory
     */
    protected $_registryFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magenest\GiftRegistry\Model\RegistrantFactory $eventFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magenest\GiftRegistry\Model\RegistrantFactory $eventFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_registryFactory= $eventFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('registryGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_registryFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     */

    protected function _prepareColumns()
    {

        $this->addColumn(
            'registrant_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'registrant_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'type' => 'text',
                'index' => 'email'
            ]
        );
        $this->addColumn(
            'firstname',
            [
                'header' => __('First Name'),
                'type' => 'text',
                'index' => 'firstname'
            ]
        );
        $this->addColumn(
            'lastname',
            [
                'header' => __('Last Name'),
                'type' => 'text',
                'index' => 'lastname'
            ]
        );
        $this->addColumn(
            'created_time',
            [
                'header' => __('Created at'),
                'type' => 'text',
                'index' => 'created_time'
            ]
        );
        $this->addColumn(
            'updated_time',
            [
                'header' => __('Updated Time'),
                'type' => 'text',
                'index' => 'updated_time'
            ]
        );


        $this->addColumn(
            'edit',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => [
                            'base' => '*/*/edit'
                        ],
                        'field' => 'registrant_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );


        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('event_id');
        $this->getMassactionBlock()->setTemplate('Magento_Backend::widget/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('registrant_id');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('giftregistrys/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('giftregistrys/*/grid', ['_current' => true]);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            'giftregistrys/*/edit',
            ['registrant_id' => $row->getId()]
        );
    }
}
