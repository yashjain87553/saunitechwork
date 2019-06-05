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

class OrderSaveAfter extends Order
{
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
        /** @var \Magento\Sales\Model\Order $order */
        if (!$order = $observer->getEvent()->getOrder()) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }
        $status = $order->getStatus();

        if (\Magento\Sales\Model\Order::STATE_COMPLETE == $status && $order->getCustomerId()) {
            $this->customerTierService->updateCustomerTier($order->getCustomerId());
        }
        if (in_array($status, $this->getConfig()->getGeneralEarnInStatuses())) {
            $this->earnOrderPoints($order);
        }

        /** compatibility with PSP MultiSafepay. They do not call order cancel event */
        if (\Magento\Sales\Model\Order::STATE_CANCELED == $status && $this->_isOrderPaidNow($order)) {
            if ($order->getCustomerId()) {
                $this->rewardsBalanceOrder->restoreSpendPoints($order);
            }
        }
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
