<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 22:24
 */

namespace Magenest\GiftRegistry\Model\ResourceModel;

/**
 * Class Type
 * @package Magenest\GiftRegistry\Model\ResourceModel
 */
class Type extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('magenest_giftregistry_event_type', 'id');
    }
}
