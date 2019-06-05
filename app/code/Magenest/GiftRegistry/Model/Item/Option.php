<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 22/02/2016
 * Time: 00:46
 */
namespace Magenest\GiftRegistry\Model\Item;

/**
 * Class Option
 * @package Magenest\GiftRegistry\Model\Item
 */
class Option extends \Magento\Framework\Model\AbstractModel implements \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
{

    /**
     * Option constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Item\Option $resource
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Item\Option\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magenest\GiftRegistry\Model\ResourceModel\Item\Option $resource,
        \Magenest\GiftRegistry\Model\ResourceModel\Item\Option\Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve value associated with this option
     *
     * @return mixed
     */
    public function getValue()
    {
        $value = $this->_getData('value');
        return $value;
    }
}
