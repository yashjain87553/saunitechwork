<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 22:51
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Type;

/**
 * Class Grid
 * @package Magenest\GiftRegistry\Block\Adminhtml\Type
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magenest\GiftRegistry\Model\Status
     */
    protected $_status;

    /**
     * @var \Magenest\GiftRegistry\Model\TypeFactory
     */
    protected $_typeFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magenest\GiftRegistry\Model\TypeFactory $typeFactory
     * @param \Magenest\GiftRegistry\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magenest\GiftRegistry\Model\TypeFactory $typeFactory,
        \Magenest\GiftRegistry\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_typeFactory = $typeFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('typeGrid');
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
        $collection = $this->_typeFactory->create()->getCollection();
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
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'event_type',
            [
                'header' => __('Event Type'),
                'type' => 'text',
                'index' => 'event_type'
            ]
        );
        $this->addColumn(
            'event_title',
            [
                'header' => __('Event Title'),
                'type' => 'text',
                'index' => 'event_title'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_status->getOptionArrayActive()
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
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit'
                        ],
                        'field' => 'id'
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
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setTemplate('Magento_Backend::widget/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('type');

        $statuses = $this->_status->getOptionArrayActive();
        $this->getMassactionBlock()->addItem(
            'active',
            [
                'label' => __('Change active'),
                'url' => $this->getUrl('giftregistrys/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'active',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Is Active'),
                        'values' => $statuses
                    ]
                ]
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
            ['id' => $row->getId()]
        );
    }
}
