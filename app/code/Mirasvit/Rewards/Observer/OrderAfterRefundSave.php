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

class OrderAfterRefundSave extends Order
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        /** @var \Magento\Sales\Model\Order_Creditmemo $creditMemo */
        if (!$creditMemo = $observer->getEvent()->getCreditmemo()) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create()->load($creditMemo->getOrderId());
        if ($this->getConfig()->getGeneralIsCancelAfterRefund()) {
            $this->rewardsBalanceOrder->cancelEarnedPoints($order, $creditMemo);
        }

        if ($this->getConfig()->getGeneralIsRestoreAfterRefund()) {
            $this->rewardsBalanceOrder->restoreSpendPoints($order, $creditMemo);
        }
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
        return;
    }
}
