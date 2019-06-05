<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Model\ResourceModel\Item as ResourceModel;

/**
 * Class Item
 * @package Magenest\GiftRegistry\Model
 */
class Item extends \Magento\Framework\Model\AbstractModel implements \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface
{
    protected $_eventPrefix = 'giftregistry_item';

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magenest\GiftRegistry\Helper\Data
     */
    protected $helper ;

    /**
     * Item constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel $resource
     * @param ResourceModel\Collection $resourceCollection
     * @param \Magenest\GiftRegistry\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magenest\GiftRegistry\Model\ResourceModel\Item $resource,
        \Magenest\GiftRegistry\Model\ResourceModel\Item\Collection $resourceCollection,
        \Magenest\GiftRegistry\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        array $data = []
    ) {
    
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productRepository = $productRepositoryInterface;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct()
    {
        $productId = $this->getData('product_id');

        $product = $this->productRepository->getById($productId);

        $customOptionsArr = $this->helper->getCustomOptionAsArr($this->getId());
        $product->setCustomOptions($customOptionsArr);
        return $product;
    }

    /**
     * Get item option by code
     *
     * @param  string $code
     * @return \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
     */
    public function getOptionByCode($code)
    {
        $option = $this->helper->getOptionByCode($this->getId(), $code);
        return $option;
    }

    /**
     * Returns special download params (if needed) for custom option with type = 'file''
     * Return null, if not special params needed'
     * Or return \Magento\Framework\DataObject with any of the following indexes:
     *  - 'url' - url of controller to give the file
     *  - 'urlParams' - additional parameters for url (custom option id, or item id, for example)
     *
     * @return null|\Magento\Framework\DataObject
     */
    public function getFileDownloadParams()
    {
        // TODO: Implement getFileDownloadParams() method.
    }
}
