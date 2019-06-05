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


namespace Mirasvit\Rewards\Model\Total\Invoice;

class Discount extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    private $rewardsPurchase;

    public function __construct(
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        array $data = []
    ) {
        parent::__construct($data);

        $this->rewardsPurchase  = $rewardsPurchase;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        parent::collect($invoice);

        $order = $invoice->getOrder();
        $purchase = $this->rewardsPurchase->getByOrder($order);

        if (!$purchase) {
            return $this;
        }

        $invoice->setBaseTotalAmount($this->getCode(), -$purchase->getBaseSpendAmount());
        $invoice->setTotalAmount($this->getCode(), -$purchase->getSpendAmount());
        $invoice->setBaseRewardsDiscount($purchase->getBaseSpendAmount());
        $invoice->setRewardsDiscount($purchase->getSpendAmount());

        if ($invoice->getBaseGrandTotal()) {
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $purchase->getBaseSpendAmount());
        }
        if ($invoice->getGrandTotal()) {
            $invoice->setGrandTotal($invoice->getGrandTotal() - $purchase->getSpendAmount());
        }

        return $this;
    }
}
