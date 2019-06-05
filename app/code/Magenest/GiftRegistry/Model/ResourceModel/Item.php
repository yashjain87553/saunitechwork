<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model\ResourceModel;

/**
 * Class Item
 * @package Magenest\GiftRegistry\Model\ResourceModel
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magenest_giftregistry_item', 'gift_item_id');
    }
}
