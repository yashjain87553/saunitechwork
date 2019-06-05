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



namespace Mirasvit\Rewards\Observer\Payment\Model\Cart;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SetRewardsDiscount implements ObserverInterface
{
    private $purchaseHelper;

    public function __construct(
        \Mirasvit\Rewards\Helper\Purchase $purchaseHelper
    ) {
        $this->purchaseHelper = $purchaseHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Paypal\Model\Cart $cart */
        $cart = $observer->getCart();

        $quoteId = $cart->getSalesModel()->getDataUsingMethod('entity_id');
        if (!$quoteId) {
            $quoteId = $cart->getSalesModel()->getDataUsingMethod('quote_id');
        }
        $purchase = $this->purchaseHelper->getByQuote($quoteId);

        if ($purchase && $purchase->getSpendAmount() > 0) {
            $cart->addCustomItem(
                'Rewards Discount',
                1,
                -$purchase->getSpendAmount()
            );
        }
    }
}
