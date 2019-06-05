<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Model\ResourceModel\Registrant as ResourceModel;

/**
 * Class Registrant
 * @package Magenest\GiftRegistry\Model
 */
class Registrant extends \Magento\Framework\Model\AbstractModel
{
    protected $_eventPrefix = 'giftregistry_owner';

    /**
     * Registrant constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel $resource
     * @param ResourceModel\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magenest\GiftRegistry\Model\ResourceModel\Registrant $resource,
        \Magenest\GiftRegistry\Model\ResourceModel\Registrant\Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
}
