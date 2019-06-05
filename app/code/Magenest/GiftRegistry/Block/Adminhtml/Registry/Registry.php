<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 23:45
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry;

/**
 * Class Registry
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry
 */
class Registry extends \Magento\Backend\Block\Widget\Grid\Container
{
    
    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_GiftRegistry';

        parent::_construct();
        $this->removeButton('add');
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Magenest\GiftRegistry\Block\Adminhtml\Registry\Grid', 'giftregistry.registry.grid')
        );
        return parent::_prepareLayout();
    }
}
