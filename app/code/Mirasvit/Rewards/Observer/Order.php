<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   2.3.12
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rewards\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class Order implements ObserverInterface
{
    protected $cartFactory;
    protected $config;
    protected $context;
    protected $customerTierService;
    protected $messageManager;
    protected $orderFactory;
    protected $quoteCollectionFactory;
    protected $registry;
    protected $resource;
    protected $rewardsBalanceEarn;
    protected $rewardsBalanceOrder;
    protected $rewardsBehavior;
    protected $rewardsData;
    protected $rewardsPurchase;
    protected $rewardsReferral;
    protected $resourceCollection;
    protected $sessionFactory;
    protected $typeOnepage;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Checkout\Model\Type\Onepage $typeOnepage,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Helper\Referral $rewardsReferral,
        \Mirasvit\Rewards\Helper\Balance\Order $rewardsBalanceOrder,
        \Mirasvit\Rewards\Helper\Balance\Earn $rewardsBalanceEarn,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Mirasvit\Rewards\Helper\Behavior $rewardsBehavior,
        \Mirasvit\Rewards\Service\Customer\Tier $customerTierService,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null
    ) {
        $this->sessionFactory         = $sessionFactory;
        $this->cartFactory            = $cartFactory;
        $this->orderFactory           = $orderFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->config                 = $config;
        $this->typeOnepage            = $typeOnepage;
        $this->rewardsPurchase        = $rewardsPurchase;
        $this->rewardsReferral        = $rewardsReferral;
        $this->rewardsBalanceOrder    = $rewardsBalanceOrder;
        $this->rewardsBalanceEarn     = $rewardsBalanceEarn;
        $this->rewardsData            = $rewardsData;
        $this->rewardsBehavior        = $rewardsBehavior;
        $this->customerTierService    = $customerTierService;
        $this->registry               = $registry;
        $this->messageManager         = $messageManager;
        $this->context                = $context;
        $this->resource               = $resource;
        $this->resourceCollection     = $resourceCollection;
    }

    /**
     * @return \Mirasvit\Rewards\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param bool                       $force
     * @param bool                       $freezeSpendPoints
     *
     * @return void
     */
    protected function refreshPoints($quote, $force = false, $freezeSpendPoints = false)
    {
        if ($quote->getIsPurchaseSave() && !$force) {
            return;
        }

        if (!$purchase = $this->rewardsPurchase->getByQuote($quote)) {
            return;
        }

        if (
            ($this->context->getAppState()->getAreaCode() == 'frontend' &&
            !($this->sessionFactory->create()->isLoggedIn() && $this->sessionFactory->create()->getId())) ||
            !$quote->getAllItems()
        ) {
            $purchase->setSpendPoints(0);
        }
        $purchase->setQuote($quote);
        $purchase->setFreezeSpendPoints($freezeSpendPoints);
        $purchase->refreshPointsNumber($force);
        $purchase->save();

        $this->rewardsReferral->rememberReferal($quote);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    protected function _isOrderPaidNow($order)
    {
        $name = 'mst_ordercompleted_done_'.$order->getId();
        if (!$this->registry->registry($name)) {
            $this->registry->register($name, true);

            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return void
     */
    protected function earnOrderPoints($order)
    {
        if ($order->getCustomerId()) {
            $this->rewardsBehavior->processRule(
                \Mirasvit\Rewards\Model\Config::BEHAVIOR_TRIGGER_CUSTOMER_ORDER,
                $order->getCustomerId(),
                $this->rewardsData->getWebsiteId($order->getStoreId()),
                $order->getId(),
                ['order' => $order]
            );
            $this->rewardsBalanceOrder->earnOrderPoints($order);
        }
        $this->rewardsReferral->processReferralOrder($order);
    }
}
