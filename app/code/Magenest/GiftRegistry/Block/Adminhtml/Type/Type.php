<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 22:51
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Type;

/**
 * Class Type
 * @package Magenest\GiftRegistry\Block\Adminhtml\Type
 */
class Type extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_GiftRegistry';
        $this->_controller = 'adminhtml_type';
        $this->_headerText = __('Gift Registry');
        $this->_addButtonLabel = __('Add Event Type');
        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Magenest\GiftRegistry\Block\Adminhtml\Type\Grid', 'giftregistry.type.grid')
        );
        return parent::_prepareLayout();
    }
}
