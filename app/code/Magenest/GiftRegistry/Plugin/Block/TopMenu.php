<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 08/07/2017
 * Time: 14:35
 */

namespace Magenest\GiftRegistry\Plugin\Block;

use Magento\Framework\Data\Tree\NodeFactory;

/**
 * Class TopMenu
 * @package Magenest\GiftRegistry\Plugin\Block
 */
class TopMenu
{
    /**
     * @var NodeFactory
     */

    protected $_nodeFactory;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    /**
     * TopMenu constructor.
     * @param NodeFactory $nodeFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        NodeFactory $nodeFactory
    ) {
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_nodeFactory = $nodeFactory;
    }

    /**
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     */
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        $node = $this->_nodeFactory->create(
            [
                'data' => $this->getNodeAsArray(),
                'idField' => 'id',
                'tree' => $subject->getMenu()->getTree()
            ]
        );
        $subject->getMenu()->addChild($node);
    }

    /**
     * @return array
     */
    protected function getNodeAsArray()
    {
        return [
            'name' => __('Gift Registry'),
            'id' => 'giftregistry',
            'url' => $this->_urlBuilder->getUrl('giftregistry.html'),
            'has_active' => false,
            'is_active' => false // (expression to determine if menu item is selected or not)
        ];
    }
}
