<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 11:32
 */

namespace Magenest\GiftRegistry\Block\Adminhtml\Type\Edit\Tab;

/**
 * Class Main
 * @package Magenest\GiftRegistry\Block\Adminhtml\Type\Edit\Tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magenest\GiftRegistry\Model\Status
     */
    protected $_status;

    /**
     * @var \Magenest\GiftRegistry\Model\TypeFactory
     */
    protected $_typeFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param \Magento\Framework\Registry              $registry
     * @param \Magento\Framework\Data\FormFactory      $formFactory
     * @param \Magento\Store\Model\System\Store        $systemStore
     * @param \Magenest\GiftRegistry\Model\Status      $status
     * @param \Magenest\GiftRegistry\Model\TypeFactory $typeFactory
     * @param array                                    $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magenest\GiftRegistry\Model\Status $status,
        \Magenest\GiftRegistry\Model\TypeFactory $typeFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->_typeFactory = $typeFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return                                        $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('type');

        /**
         * @var \Magento\Framework\Data\Form $form
         */
        $form = $this->_formFactory->create();
        // $form->setHtmlIdPrefix('page_');


        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Event Information')]);

        if ($model->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }

        $event_type = $model->getData('event_type');
        if ($event_type == 'birthdaygift' || $event_type == 'babygift' || $event_type == 'weddinggift' || $event_type == 'christmasgift') {
            $fieldset->addField(
                'event_type',
                'text',
                [
                    'name' => 'event_type',
                    'label' => __('Event Code'),
                    'title' => __('Event Code'),
                    'required' => true,
                    'readonly' => true,
                ]
            );
        } else {
            $fieldset->addField(
                'event_type',
                'text',
                [
                    'name' => 'event_type',
                    'label' => __('Event Code'),
                    'title' => __('Event Code'),
                    'required' => true,
                ]
            );
        }

        $fieldset->addField(
            'event_title',
            'text',
            [
                'name' => 'event_title',
                'label' => __('Event Title'),
                'title' => __('Event Title'),
                'required' => true,
            ]
        );

        // Setting custom renderer for content field to remove label column
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => $this->_status->getOptionArray(),
            ]
        );
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
        //        $form->setValues($model->getData());
        //        $this->setForm($form);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Event Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Event Information');
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
