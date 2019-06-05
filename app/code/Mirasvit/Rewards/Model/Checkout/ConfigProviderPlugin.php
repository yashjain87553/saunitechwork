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


/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mirasvit\Rewards\Model\Checkout;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

class ConfigProviderPlugin
{
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilber,
        \Mirasvit\Rewards\Helper\Message $messageHelper,
        \Mirasvit\Rewards\Helper\Rule\Notification $rewardsNotification,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Mirasvit\Rewards\Model\Config $config,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->urlBuilber          = $urlBuilber;
        $this->messageHelper       = $messageHelper;
        $this->rewardsNotification = $rewardsNotification;
        $this->rewardsPurchase     = $rewardsPurchase;
        $this->rewardsData         = $rewardsData;
        $this->config              = $config;
        $this->checkoutSession     = $checkoutSession;
        $this->customerSession     = $customerSession;
        $this->scopeConfig         = $scopeConfig;
        $this->moduleManager       = $moduleManager;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array                                         $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        $storeId = null;
        $result['min_point_to_redeem']=$this->scopeConfig->getValue('rewards/pww_options/min_points_to_redem_points', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $result['chechoutRewardsIsShow']         = 0;
        $result['chechoutRewardsPoints']         = 0;
        $result['chechoutRewardsPointsMax']      = 0;
        $result['chechoutRewardsPointsSpend']    = 0;
        $result['chechoutRewardsPointsAvailble'] = 0;
        $result['chechoutRewardsPointsName']     = $this->rewardsData->getPointsName();
        if (($purchase = $this->rewardsPurchase->getPurchase()) && $purchase->getQuote()->getCustomerId()) {
            $purchase->refreshPointsNumber(true);
            $result['chechoutRewardsNotificationMessages'] = [];
            if ($purchase->getEarnPoints()) {
                $result['chechoutRewardsPoints'] = $this->rewardsData->formatPoints($purchase->getEarnPoints());
            }
            if ($point = $purchase->getSpendPoints()) {
                $result['chechoutRewardsPointsSpend'] = $this->rewardsData->formatPoints($point);
                $result['chechoutRewardsPointsUsed']  = $point;
            }
            $quote = $purchase->getQuote();
            $result['chechoutRewardsPointsAvailble'] = $this->rewardsData->formatPoints(
                $purchase->getCustomerBalancePoints($quote->getCustomerId())
            );
            $result['chechoutRewardsPointsMax']      = $purchase->getMaxPointsNumberToSpent();
            $result['chechoutRewardsIsShow']         = (bool)$result['chechoutRewardsPointsMax'];
        } else {
            $quote = $this->checkoutSession->getQuote();
        }

        $result['chechoutRewardsApplayPointsUrl'] = $this->urlBuilber->getUrl(
            'rewards/checkout/applyPointsPost', ['_secure' => true]
        );

        $result['chechoutRewardsPaymentMethodPointsUrl'] = $this->urlBuilber->getUrl(
            'rewards/checkout/updatePaymentMethodPost', ['_secure' => true]
        );
        if ($quote) {
            $storeId = $quote->getStoreId();
        }
        $message = $this->config->getDisplayOptionsCheckoutNotification($storeId);
        if ($message) {
            $result['rewardsCheckoutNotification'] = $this->messageHelper->processCheckoutNotificationVariables(
                $message
            );
        } else {
            $result['rewardsCheckoutNotification'] = '';
        }
        $result['isMageplazaOsc'] = (int)$this->moduleManager->isEnabled('Mageplaza_Osc');
        $result['isMinOrderSet'] = 0;
        if ($purchase->getQuote()) {
            $result['isMinOrderSet'] = (int)$this->isMinOrderSet($purchase->getQuote());
        }

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    protected function isMinOrderSet($quote)
    {
        $minOrderActive = $this->scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );
        $minAmount = $this->scopeConfig->getValue(
            'sales/minimum_order/amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );

        return $minOrderActive && $minAmount;
    }
}
