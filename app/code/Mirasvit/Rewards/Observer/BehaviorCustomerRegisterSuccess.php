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
use Magento\Sales\Api\OrderRepositoryInterface;
use Mirasvit\Rewards\Model\Config;

class BehaviorCustomerRegisterSuccess implements ObserverInterface
{
    private $orderRepository;
    private $productMetadata;
    private $session;
    private $balanceOrderHelper;
    private $referralFactory;
    private $referralCollectionFactory;
    private $subscriberModel;
    private $rewardsBehavior;
    private $balanceHelper;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Mirasvit\Rewards\Helper\Balance\Order $balanceOrderHelper,
        \Mirasvit\Rewards\Model\ReferralFactory $referralFactory,
        \Mirasvit\Rewards\Model\ResourceModel\Referral\CollectionFactory $referralCollectionFactory,
        \Magento\Newsletter\Model\ResourceModel\Subscriber $subscriberModel,
        \Mirasvit\Rewards\Helper\Behavior $rewardsBehavior,
        \Mirasvit\Rewards\Helper\Balance $balanceHelper
    ) {
        $this->orderRepository           = $orderRepository;
        $this->productMetadata           = $productMetadata;
        $this->session                   = $session;
        $this->balanceOrderHelper        = $balanceOrderHelper;
        $this->referralFactory           = $referralFactory;
        $this->referralCollectionFactory = $referralCollectionFactory;
        $this->subscriberModel           = $subscriberModel;
        $this->rewardsBehavior           = $rewardsBehavior;
        $this->balanceHelper             = $balanceHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }

        /** @var \Magento\Customer\Model\Customer $customer */
        $customerDataObject = $observer->getEvent()->getCustomerDataObject();
        $origCustomerDataObject = $observer->getEvent()->getOrigCustomerDataObject();
        $mVersion = $this->productMetadata->getVersion();
        $isExit = version_compare($mVersion, '2.2.2') < 0 && !$origCustomerDataObject;
        if ($isExit || !$customerDataObject->getId()) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }
        if ($customerDataObject->getId() && ($origCustomerDataObject && $origCustomerDataObject->getId())) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }

        $this->applyRules($customerDataObject);

        // for m2.2.5. Added points for the order when customer created an account at the end of checkout
        // for early versions used plugin OrderCustomerManagement
        $delegateData = $observer->getEvent()->getData('delegate_data');
        if ($delegateData && array_key_exists('__sales_assign_order_id', $delegateData)) {
            $orderId = $delegateData['__sales_assign_order_id'];
            $order = $this->orderRepository->get($orderId);
            if ($order->getId()) {
                $this->balanceOrderHelper->earnBehaviorOrderPoints($order);
            }
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     */
    protected function applyRules($customer)
    {
        $this->customerAfterCreate($customer);
        $this->rewardsBehavior->processRule(Config::BEHAVIOR_TRIGGER_SIGNUP, $customer);
        if ($this->isCustomerSubscribed($customer)) {
            $this->rewardsBehavior->processRule(Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP, $customer);
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return bool
     */
    protected function isCustomerSubscribed($customer)
    {
        $subscribed = false;
        $subscriber = $this->subscriberModel->loadByEmail($customer->getEmail());
        if ($subscriber && $subscriber['subscriber_status']) {
            $subscribed = true;
        }

        return $subscribed;
    }

    /**
     * Customer sign up.
     *
     * @param \Magento\Customer\Model\Customer $customer
     *
     * @return void
     */
    public function customerAfterCreate($customer)
    {
        $referral = false;
        if ($id = (int) $this->session->getReferral()) {
            /** @var \Mirasvit\Rewards\Model\Referral $referral */
            $referral = $this->referralFactory->create()->load($id);
        } else {
            $referrals = $this->referralCollectionFactory->create()
                ->addFieldToFilter('email', $customer->getEmail());
            if ($referrals->count()) {
                $referral = $referrals->getFirstItem();
            }
        }
        if (!$referral) {
            return;
        }
        $referral->finish(Config::REFERRAL_STATUS_SIGNUP, $customer->getId());
        /** @var \Mirasvit\Rewards\Model\Transaction $transaction */
        $transaction = $this->rewardsBehavior->processRule(
            Config::BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_SIGNUP,
            $referral->getCustomerId(),
            false,
            $customer->getId()
        );
        $referral->finish(Config::REFERRAL_STATUS_SIGNUP, false, $transaction);
    }
}
