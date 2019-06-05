<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 8/4/17
 * Time: 8:49 AM
 */

namespace Magenest\GiftRegistry\Block\Adminhtml\Type\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as FormGeneric;

/**
 * Class Images
 * @package Magenest\GiftRegistry\Block\Adminhtml\Type\Edit\Tab
 */
class Images extends FormGeneric implements \Magento\Backend\Block\Widget\Tab\TabInterface
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

        $form->setHtmlIdPrefix('images_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Background Image')]);

        $fieldset->addType('image', 'Magenest\GiftRegistry\Block\Adminhtml\Helper\Image');

        $sizeHint = '<p style="color:red;position: absolute;left: 194px;width: 300px;top: 54px;" class="label" id="error-type-file"></p>';
        $sizeHint.=
        '<script>
        require([
        "jquery",
        "mage/backend/tabs"
        ], function ($) {
        "use strict";
            $("#images_image").change(function () {
            var file = $("#images_image").val();
            if (file == "") {
                $("#error-type-file").empty();
                $("#error-type-file").append("Please add the background image!");
            } else {
                var ext = file.split(".");
                ext = ext[ext.length-1].toLowerCase();
                var arrayExtensions = ["jpg" , "jpeg", "png"];
                if (arrayExtensions.lastIndexOf(ext) == -1) {
                    $("#error-type-file").append("Only accept png,jpeg,jpg extension!");
                    $("#images_image").val("");
                } else {
                    $("#error-type-file").empty();
                }
            }
            });
        });
        </script>';
        $fieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Background Image'),
                'title' => __('Background Image'),
                'required' => true,
                'after_element_html' => $sizeHint,
                'enctype'=> 'multipart/form-data',
                'note' => 'Allow image type: jpg, jpeg, png (Optimal icon min size is 1600 x 1100 px)'
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
        return __('Background Image');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Background Image');
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
