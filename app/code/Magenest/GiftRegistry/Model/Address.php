<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 22/02/2016
 * Time: 01:27
 */

namespace Magenest\GiftRegistry\Model;

/**
 * Class Address
 * @package Magenest\GiftRegistry\Model
 */
class Address extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Address constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Address $resource
     * @param ResourceModel\Address\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magenest\GiftRegistry\Model\ResourceModel\Address $resource,
        \Magenest\GiftRegistry\Model\ResourceModel\Address\Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
}
