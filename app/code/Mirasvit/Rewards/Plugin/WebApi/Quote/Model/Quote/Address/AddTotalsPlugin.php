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


namespace Mirasvit\Rewards\Plugin\WebApi\Quote\Model\Quote\Address;

use Magento\Quote\Model\Quote\Address;
use Mirasvit\Rewards\Helper\Purchase;

/**
 * @package Mirasvit\Rewards\Plugin\WebApi
 */
class AddTotalsPlugin
{
    public function __construct(Purchase $purchaseHelper)
    {
        $this->purchaseHelper = $purchaseHelper;
    }

    /**
     * @param Address $address
     * @param \callable $proceed
     * @return array
     */
    public function aroundGetTotals(Address $address, $proceed)
    {
        $quote = $address->getQuote();
        $purchase = $this->purchaseHelper->getByQuote($quote->getId());
        if ($quote->getCustomerId() > 0 && $purchase->getId()) {
            $totalEarn = [
                'code'  => 'rewards-total',
                'title' => __('You Earn'),
                'value' => $purchase->getEarnPoints(),
            ];
            $address->addTotal($totalEarn);
            $totalSpendPoints = [
                'code'  => 'rewards-spend',
                'title' => __('You Spend Points'),
                'value' => $purchase->getSpendPoints(),
            ];
            $address->addTotal($totalSpendPoints);
            $spendAmount = -$purchase->getSpendAmount();
            $totalSpendAmount = [
                'code'  => 'rewards-spend-amount',
                'title' => __('Spend Amount'),
                'value' => $spendAmount,
            ];
            $address->addTotal($totalSpendAmount);
            $totalSpendAmount = [
                'code'  => 'rewards-deduction',
                'title' => __('Rewards Discount4'),
                'value' => $spendAmount,
            ];
            $address->addTotal($totalSpendAmount);
            $totalSpendAmount = [
                'code'  => 'rewards-spend-min-points',
                'title' => __('Spend Minimum Points'),
                'value' => $purchase->getSpendMinPoints(),
            ];
            $address->addTotal($totalSpendAmount);
            $totalSpendAmount = [
                'code'  => 'rewards-spend-max-points',
                'title' => __('Spend Maximum Points'),
                'value' => $purchase->getSpendMaxPoints(),
            ];
            $address->addTotal($totalSpendAmount);
        }

        return $proceed();
    }
}