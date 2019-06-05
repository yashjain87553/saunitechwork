<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 08:58
 */
namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Model\ResourceModel\Tran as ResourceTran;
use Magenest\GiftRegistry\Model\ResourceModel\Tran\Collection as Collection;

/**
 * Class Tran
 * @package Magenest\GiftRegistry\Model
 */
class Tran extends \Magento\Framework\Model\AbstractModel
{
    protected $_eventPrefix = 'tran';

    /**
     * Tran constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceTran $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ResourceTran $resource,
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
