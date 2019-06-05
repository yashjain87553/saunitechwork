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



namespace Mirasvit\Rewards\Block\Checkout\Cart;

/**
 * Displays rewards form on cart page
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RewardsPoints extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->rewardsPurchase  = $rewardsPurchase;
        $this->config           = $config;
        $this->configProvider   = $configProvider;
        $this->layoutProcessors = $layoutProcessors;

        parent::__construct($context, $customerSession, $checkoutSession, $data);

        $this->_isScopePrivate = true;
    }

    /**
     * @return bool|\Mirasvit\Rewards\Model\Purchase
     */
    protected function getPurchase()
    {
        $purchase = $this->rewardsPurchase->getByQuote($this->getQuote());

        return $purchase;
    }

    /**
     * @return string
     */
    public function getNotificationMessage()
    {
        return $this->config->getDisplayOptionsCheckoutNotification($this->getQuote()->getStore());
    }

    /**
     * @return int
     */
    public function getMaxPointsNumberToSpent()
    {
        if (!$this->getPurchase()) {
            return 0;
        }

        return $this->getPurchase()->getMaxPointsNumberToSpent();
    }

    /**
     * Retrieve checkout configuration
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Retrieve serialized JS layout configuration ready to use in template
     *
     * @return string
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * Get base url for block.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}
