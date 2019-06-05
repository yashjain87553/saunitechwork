<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 22:24
 */

namespace Magenest\GiftRegistry\Model\ResourceModel\Type;

/**
 * Class Collection
 * @package Magenest\GiftRegistry\Model\ResourceModel\Type
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('Magenest\GiftRegistry\Model\Type', 'Magenest\GiftRegistry\Model\ResourceModel\Type');
    }

    /**
     * @return $this
     */
    public function getActiveEventType()
    {
        $this->addFilter('status', 1);
        return $this;
    }
}
