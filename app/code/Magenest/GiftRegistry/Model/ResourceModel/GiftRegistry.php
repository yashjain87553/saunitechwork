<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model\ResourceModel;

/**
 * Class GiftRegistry
 * @package Magenest\GiftRegistry\Model\ResourceModel
 */
class GiftRegistry extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magenest_giftregistry', 'gift_id');
    }
}
