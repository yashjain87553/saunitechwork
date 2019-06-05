<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 29/12/2015
 * Time: 11:41
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class Add
 * @package Magenest\GiftRegistry\Controller\Index
 */
class Add extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Action\Context
     */
    protected $_context;

    /**
     * @var \Magenest\GiftRegistry\Controller\GiftRegistryProviderInterface
     */
    protected $_giftRegistryProviderProvider;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $_currentCustomer;

    /**
     * @var GiftRegistryFactory
     */
    protected $_registryFactory;

    /**
     * Add constructor.
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magenest\GiftRegistry\Controller\GiftRegistryProviderInterface $giftRegistryProvider
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magenest\GiftRegistry\Controller\GiftRegistryProviderInterface $giftRegistryProvider,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        ProductRepositoryInterface $productRepository
    ) {
    
        $this->_registryFactory = $giftRegistryFactory;
        $this->_context = $context;
        $this->_customerSession = $customerSession;
        $this->_giftRegistryProviderProvider = $giftRegistryProvider;
        $this->_productRepository = $productRepository;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_currentCustomer = $currentCustomer;
        parent::__construct($context);
    }

    /**
     * @return $this
     * @throws NotFoundException
     * @throws \Magento\Framework\Validator\Exception
     */
    public function execute()
    {
        /**
         * @var  $giftRegistry  \Magenest\GiftRegistry\Model\GiftRegistry
         */
        $giftRegistry = $this->_giftRegistryProviderProvider->getGiftRegistry();
        if (!$giftRegistry) {
            throw new NotFoundException(__('Page not found.'));
        }
        $params = $this->getRequest()->getParams();
        $giftRegistryId = 0;

        if (!isset($params['product'])) {
            throw new   \Magento\Framework\Validator\Exception(__('You have to specify a product.'));
        }

        if (isset($params['giftregistry'])) {
            $giftRegistryId = intval($params['giftregistry']);

            if (!is_numeric($giftRegistryId)) {
                //forward the customer to customer account page
                /**
                 * @var \Magento\Backend\Model\View\Result\Forward $resultForward
                 */
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->setModule('customer');

                $resultForward->setController('account');

                return $resultForward->forward('create');
            }
        }

        $productId = (int)$params['product'];
        try {
            $product = $this->_productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }
        $buyRequest = new \Magento\Framework\DataObject($params);
        $checkGuest = $this->_currentCustomer->getCustomerId();

        if ($checkGuest) {
            try {
                $giftRegistry->addNewItem($product, $buyRequest);
                $registry = $this->_registryFactory->create()->load($giftRegistryId);
//                $resultData = ['type' => 'success', 'message' => __('You have add an item to your gift registry successfully.')];
                $this->messageManager->addSuccess(__('The item is added to your gift registry.'));
                return $this->resultRedirectFactory->create()->setPath('giftregistrys/index/manageregistry', ['type' => $registry->getData('type'), 'event_id' => $giftRegistryId,]);
            } catch (\Exception $e) {
                $resultData = ['type' => 'error', 'message' => $e->getMessage()];
            }
        }
        return $this->resultRedirectFactory->create()->setPath('customer/account/login');
    }
}
