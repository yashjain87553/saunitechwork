<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 30/11/2015
 * Time: 11:38
 */
namespace Magenest\GiftRegistry\Block\Item;

/**
 * Class Add
 * @package Magenest\GiftRegistry\Block\Item
 */
class Add extends \Magento\Framework\View\Element\Template
{
    /**
     * Template name
     *
     * @var string
     */
    protected $_template = 'Magenest_GiftRegistry::link.phtml';

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_registryHelper;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $coreRegistry
     * @param \Magenest\GiftRegistry\Helper\Data               $helper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magenest\GiftRegistry\Helper\Data $helper,
        array $data = []
    ) {
        $this->_registryHelper = $helper;
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_registryHelper->isAllow()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('giftregistrys/guest/search');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('My Gift Registry');
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->_coreRegistry->registry('product') && $this->getProductId()) {
            $product = $this->productRepository->getById($this->getProductId());
            $this->_coreRegistry->register('product', $product);
        }
        return $this->_coreRegistry->registry('product');
    }
}
