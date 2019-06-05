<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 22/02/2016
 * Time: 00:48
 */

namespace Magenest\GiftRegistry\Model\ResourceModel\Item;

/**
 * Class Option
 * @package Magenest\GiftRegistry\Model\ResourceModel\Item
 */
class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magenest_giftregistry_item_option', 'option_id');
    }
}
