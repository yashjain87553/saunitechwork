<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 30/11/2015
 * Time: 11:38
 */
namespace Magenest\GiftRegistry\Block;

/**
 * Class Link
 * @package Magenest\GiftRegistry\Block
 */
class Link extends \Magento\Framework\View\Element\Html\Link
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
     * Link constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magenest\GiftRegistry\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magenest\GiftRegistry\Helper\Data $helper,
        array $data = []
    ) {
        $this->_registryHelper = $helper;
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
}
