<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model\ResourceModel\Registrant;

/**
 * Class Collection
 * @package Magenest\GiftRegistry\Model\ResourceModel\Registrant
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'registrant_id';

    protected function _construct()
    {
        $this->_init('Magenest\GiftRegistry\Model\Registrant', 'Magenest\GiftRegistry\Model\ResourceModel\Registrant');
    }

}
