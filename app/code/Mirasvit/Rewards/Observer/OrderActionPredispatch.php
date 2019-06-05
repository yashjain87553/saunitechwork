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

class OrderActionPredispatch extends Order
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
        $uri = $observer->getControllerAction()->getRequest()->getRequestUri();
        if (strpos($uri, 'checkout') === false) {
            $this->config->setDisableRewardsCalculation(false);
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }
        if (!($quote = $this->cartFactory->create()->getQuote()) || !$quote->getId()) {
            $this->config->setDisableRewardsCalculation(false);
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }
        //this does not calculate quote correctly
        if (strpos($uri, '/checkout/cart/add/') !== false ||
            strpos($uri, '/checkout/cart/addgroup/') !== false ||
            strpos($uri, '/checkout/cart/updatePost/') !== false
        ) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            $this->config->setDisableRewardsCalculation(false);
            return;
        }
        if (
            $this->context->getAppState()->getAreaCode() == 'frontend' &&
            !($this->sessionFactory->create()->isLoggedIn() && $this->sessionFactory->create()->getId()) &&
            strpos($uri, '/checkout/cart/delete/') !== false
        ) {
            $this->config->setDisableRewardsCalculation(false);
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }
        if (strpos($uri, '/checkout/sidebar/removeItem/') !== false) {
            $this->config->setDisableRewardsCalculation(false);
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }
        if (strpos($uri, '/checkout/sidebar/updateItemQty') !== false) {
            $this->config->setDisableRewardsCalculation(false);
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }

        //this does not calculate quote correctly with firecheckout
        if (strpos($uri, '/firecheckout/') !== false) {
            $this->config->setDisableRewardsCalculation(false);
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }

        //this does not calculate quote correctly with gomage
        if (strpos($uri, '/gomage_checkout/onepage/save/') !== false) {
            $this->config->setDisableRewardsCalculation(false);
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }

        if (strpos($uri, '/checkout/onepage/success/') !== false) {
            $this->config->setDisableRewardsCalculation(false);
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }

        if (strpos($uri, '/rewards/') === 0) {
            $this->config->setDisableRewardsCalculation(false);
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }
        $this->refreshPoints($quote);
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
