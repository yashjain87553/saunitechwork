<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 26/04/2016
 * Time: 15:01
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab;

class Information extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magenest\GiftRegistry\Model\TypeFactory
     */
    protected $_itemFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param \Magento\Framework\Registry              $registry
     * @param \Magento\Framework\Data\FormFactory      $formFactory
     * @param \Magento\Store\Model\System\Store        $systemStore
     * @param \Magenest\GiftRegistry\Model\ItemFactory $itemFactory
     * @param array                                    $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magenest\GiftRegistry\Model\ItemFactory $itemFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_itemFactory = $itemFactory;
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
        $model = $this->_coreRegistry->registry('information');

        /**
 * @var \Magento\Framework\Data\Form $form
*/
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');


        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Items Registry')]);
        if ($model->getId()) {
            $fieldset->addField(
                'gift_id',
                'hidden',
                ['name' => 'gift_id']
            );
        }

        $fieldset->addField(
            'is_expired',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => false,
                'values' => [
                    ["value" => 0,"label" => __("Active")],
                    ["value" => 1,"label" => __("Expire")],
                ]
            ]
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => false,
                'value' => 'abc'
            ]
        );

        $fieldset->addField(
            'type',
            'text',
            [
                'name' => 'type',
                'label' => __('Type'),
                'title' => __('Type'),
                'required' => false,
                'value' => 'abc'
            ]
        );

        $fieldset->addField(
            'location',
            'text',
            [
                'name' => 'location',
                'label' => __('Location'),
                'title' => __('Location'),
                'required' => false,
                'value' => 'abc'
            ]
        );

        $fieldset->addField(
            'date',
            'text',
            [
                'name' => 'date',
                'label' => __('Date'),
                'title' => __('Date'),
                'required' => false,
                'value' => 'abc'
            ]
        );
        $fieldset->addField(
            'description',
            'text',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => false,
                'value' => 'abc'
            ]
        );
        $fieldset->addField(
            'privacy',
            'text',
            [
                'name' => 'privacy',
                'label' => __('Privacy'),
                'title' => __('Privacy'),
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
        return __('Information Registry');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Information Registry');
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
