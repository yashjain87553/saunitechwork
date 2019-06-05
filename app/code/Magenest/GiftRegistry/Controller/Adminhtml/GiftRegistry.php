<?php
/**
 * Created by PhpStorm.
 * User: canhnd
 * Date: 19/06/2017
 * Time: 09:24
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class GiftRegistry
 * @package Magenest\GiftRegistry\Controller\Adminhtml
 */
abstract class GiftRegistry extends Action
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $_urlRewrite;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scoreConfig;

    /**
     * @var GiftRegistryFactory
     */
    protected $_giftregistryFactory;

    /**
     * @var RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * GiftRegistry constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param GiftRegistryFactory $_giftregistryFactory
     * @param RegistrantFactory $_registranFactory
     * @param ItemFactory $_itemFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param Filter $filter
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configInterface
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        GiftRegistryFactory $_giftregistryFactory,
        RegistrantFactory $_registranFactory,
        ItemFactory $_itemFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        Filter $filter,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface
    ) {
        $this->_registrantFactory   = $_registranFactory;
        $this->_itemFactory         = $_itemFactory;
        $this->_categoryFactory     = $categoryFactory;
        $this->_productFactory      = $productFactory;
        $this->_context             = $context;
        $this->_coreRegistry        = $coreRegistry;
        $this->_resultPageFactory   = $resultPageFactory;
        $this->resultRawFactory     = $resultRawFactory;
        $this->layoutFactory        = $layoutFactory;
        $this->_giftregistryFactory = $_giftregistryFactory;
        $this->_filter              = $filter;
        $this->_urlRewrite          = $urlRewrite;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_scoreConfig         = $configInterface;
        parent::__construct($context);
    }


    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }
}
