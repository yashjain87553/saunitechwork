<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 25/12/2015
 * Time: 15:03
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab;

/**
 * Class Main
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * @var \Magenest\GiftRegistry\Model\TypeFactory
     */
    protected $_eventFactory;

    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magenest\GiftRegistry\Model\RegistrantFactory $eventFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magenest\GiftRegistry\Model\RegistrantFactory $eventFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_registryFactory= $eventFactory;
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
        $model = $this->_coreRegistry->registry('registry');

        /**
         * @var \Magento\Framework\Data\Form $form
         */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');


        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Event Information')]);
        if ($model->getId()) {
            $fieldset->addField(
                'registrant_id',
                'hidden',
                ['name' => 'registrant_id']
            );
        }


        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'title' => __('Email'),
                'required' => false,
                'value' => 'abc'
            ]
        );

        $fieldset->addField(
            'firstname',
            'text',
            [
                'name' => 'firstname',
                'label' => __('First Name'),
                'title' => __('First Name'),
                'required' => false,
                'value' => 'abc'
            ]
        );

        $fieldset->addField(
            'lastname',
            'text',
            [
                'name' => 'lastname',
                'label' => __('Last Name'),
                'title' => __('Last Name'),
                'required' => false,
                'value' => 'abc'
            ]
        );

        $fieldset->addField(
            'created_time',
            'text',
            [
                'name' => 'created_time',
                'label' => __('Created Time'),
                'title' => __('Created Time'),
                'required' => false,
                'value' => 'abc'
            ]
        );
        $fieldset->addField(
            'updated_time',
            'text',
            [
                'name' => 'updated_time',
                'label' => __('Updated Time'),
                'title' => __('Updated Time'),
                'required' => false,
                'value' => 'abc'
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __(' Account Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __(' Account Information');
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
