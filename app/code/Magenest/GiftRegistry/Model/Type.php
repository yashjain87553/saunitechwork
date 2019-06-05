<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 22:30
 */
namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Model\ResourceModel\Type as ResourceType;
use Magenest\GiftRegistry\Model\ResourceModel\Type\Collection as Collection;

/**
 * Class Type
 * @package Magenest\GiftRegistry\Model
 */
class Type extends \Magento\Framework\Model\AbstractModel
{
    protected $_eventPrefix = 'type';

    /**
     * Type constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceType $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ResourceType $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getDataCollection()
    {
        $collection = $this->getCollection()->addFieldToSelect('*');

        return $collection;
    }
}
