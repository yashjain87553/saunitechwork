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
use Magento\Newsletter\Model\Subscriber;
use Mirasvit\Rewards\Model\Config;

class BehaviorCustomerSubscribed implements ObserverInterface
{
    /**
     * @var \Mirasvit\Rewards\Helper\Behavior
     */
    protected $rewardsBehavior;

    /**
     * @param \Mirasvit\Rewards\Helper\Behavior $rewardsBehavior
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        \Mirasvit\Rewards\Helper\Behavior $rewardsBehavior,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerResourceFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->rewardsBalance  = $rewardsBalance;
        $this->rewardsBehavior = $rewardsBehavior;
        $this->customerFactory = $customerFactory;
        $this->registry        = $registry;

        $this->customerResourceFactory = $customerResourceFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        /** @var Subscriber $subscriber */
        $subscriber = $observer->getEvent()->getDataObject();

        if (($subscriber->getId() && $subscriber->isStatusChanged())) {
            if ($subscriber->getStatus() == Subscriber::STATUS_SUBSCRIBED) {
                $this->rewardsBehavior->processRule(
                    Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP, $subscriber->getCustomerId()
                );
            } elseif ($subscriber->getStatus() == Subscriber::STATUS_UNSUBSCRIBED) {
                $customer = $this->customerFactory->create();
                $this->customerResourceFactory->create()->load($customer, $subscriber->getCustomerId());
                $this->rewardsBalance->cancelEarnedPoints($customer, Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP);
            }
        }
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
