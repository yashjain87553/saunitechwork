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


namespace Mirasvit\Rewards\Block\Sales\Order;

class RewardsDiscount extends \Magento\Framework\View\Element\Template
{
    private $purchaseHelper;

    public function __construct(
        \Mirasvit\Rewards\Helper\Purchase $purchaseHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->purchaseHelper = $purchaseHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Rewards\Block\Sales\Order\RewardsDiscount
     */
    public function initTotals()
    {
        /** @var $parent \Magento\Sales\Block\Adminhtml\Order\Invoice\Totals */
        $parent = $this->getParentBlock();
        $order = $parent->getOrder();
        if (!$order || ! $order->getId()) {
            return $this;
        }
        $purchase = $this->purchaseHelper->getByOrder($order);

        if ($purchase && $purchase->getSpendAmount() > 0) {
            // Add our total information to the set of other totals
            $total = new \Magento\Framework\DataObject(
                [
                    'code'       => $this->getNameInLayout(),
                    'label'      => __('Rewards Discount'),
                    'value'      => -$purchase->getSpendAmount(),
                    'base_value' => -$purchase->getBaseSpendAmount()
                ]
            );
            if ($this->getBeforeCondition()) {
                $this->getParentBlock()->addTotalBefore($total, $this->getBeforeCondition());
            } else {
                $this->getParentBlock()->addTotal($total, $this->getAfterCondition());
            }
        }
        return $this;
    }
}
