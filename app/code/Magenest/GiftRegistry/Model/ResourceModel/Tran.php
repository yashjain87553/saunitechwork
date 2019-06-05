<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 08:57
 */
namespace Magenest\GiftRegistry\Model\ResourceModel;

/**
 * Class Tran
 * @package Magenest\GiftRegistry\Model\ResourceModel
 */
class Tran extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('magenest_giftregistry_order', 'id');
    }
}
