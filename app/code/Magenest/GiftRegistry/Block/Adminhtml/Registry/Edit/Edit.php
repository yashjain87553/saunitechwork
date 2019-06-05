<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 25/12/2015
 * Time: 15:03
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit;

/**
 * Class Edit
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize blog post edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'registrant_id';
        $this->_blockGroup = 'Magenest_GiftRegistry';
        $this->_controller = 'adminhtml_registry';
        $this->removeButton('save');
        $this->removeButton('reset');
        $this->removeButton('delete');
        parent::_construct();
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('registry')->getId()) {
            return __("Event Type '%1'", $this->escapeHtml($this->_coreRegistry->registry('registry')->getType()));
        } else {
            return __('Event Type');
        }
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('giftregistrys/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
