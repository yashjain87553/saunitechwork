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



namespace Mirasvit\Rewards\Observer\WebApi;

class SalesQuotePaymentImportDataBefore extends \Mirasvit\Rewards\Observer\Order
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $quote = $observer->getPayment()->getQuote();
        if (!$purchase = $this->rewardsPurchase->getByQuote($quote)) {
            return;
        }
        $this->refreshPoints($quote, true);
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
