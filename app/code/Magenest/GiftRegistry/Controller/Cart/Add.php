<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 19/04/2016
 * Time: 14:12
 */
namespace Magenest\GiftRegistry\Controller\Cart;

use Magento\Checkout\Model\Cart as CustomerCart;

/**
 * Class Add
 * @package Magenest\GiftRegistry\Controller\Cart
 */
class Add extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var \Magenest\GiftRegistry\Model\ItemFactory
     */
    protected $_itemFactory;

    protected $serializeHelper;

    protected $_catalogProductTypeConfigurable;

    protected $productFactory;

    public function __construct(
        \Magenest\GiftRegistry\Model\ItemFactory $itemFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        CustomerCart $cart
    ) {
    
        $this->_itemFactory = $itemFactory;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->productFactory = $productFactory;
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $itemId =(isset($params['item'])) ?$params['item']:0;
        $qty =(isset($params['qty']) && $params['qty']) ?$params['qty']:1;
        $formKey =(isset($params['formKey']) && $params['formKey']) ?$params['formKey']:'';
        $item = $this->_itemFactory->create()->load($itemId);
        if (empty($item->getData())) {
            $this->messageManager->addNotice("This item is no longer exist!");
            $resultData =['type'=>'error', 'message'=>__('This item is no longer exist!') ];
        } else {
            $buyRequestStr =  $item->getBuyRequest();
            $buyRequest = null;
            if ($buyRequestStr) {
                if ($this->checkMagentoVersion()) {
                    $this->serializeHelper = $this->_objectManager->get("\Magento\Framework\Serialize\Serializer\Serialize");
                    $buyRequest = new \Magento\Framework\DataObject($this->serializeHelper->unserialize($buyRequestStr));
                } else {
                    $buyRequest = new \Magento\Framework\DataObject(unserialize($buyRequestStr));
                }
            }
            $buyRequest->setData('qty', $qty)->setData('is_for_gift_registry', '1')->setData('registry', $item->getData('gift_id'))
                ->setData('form_key', $formKey)
                ->setData('item', $itemId);
            $product = $item->getProduct();
            try {
                $this->cart->addProduct($product, $buyRequest->toArray());
                $this->cart->save();
                $this->cart->getQuote()->save()->setTotalsCollectedFlag(false)->collectTotals();
                $this->messageManager->addSuccess(__('You have add an item from gift registry to cart successfully.'));

                $resultData =['type'=>'success', 'message'=>__('You have add item in your gift registry successfully.') ];
            } catch (\Exception $exception) {
                $resultData =['type'=>'error', 'message'=> $exception->getMessage() ];
                $this->messageManager->addErrorMessage(__('We can\'t add above item to cart.Please contact administrator.'));
            }
        }
    }

    public function checkMagentoVersion()
    {
        $magentoVersion = $this->_objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
        if (version_compare($magentoVersion, "2.2.0", ">=")) {
            return true;
        }
        return false;
    }
}
