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



namespace Mirasvit\Rewards\Helper\Output;

class Spend
{
    public function __construct(
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Helper\Balance\Spend $spendHelper,
        \Mirasvit\Rewards\Helper\Data $rewardsDataHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rewards\Helper\Balance\Earn $earnHelper
    ) {
        $this->config            = $config;
        $this->spendHelper        = $spendHelper;
        $this->rewardsDataHelper = $rewardsDataHelper;
        $this->registry          = $registry;
        $this->customerSession   = $customerSession;
        $this->storeManager   = $storeManager;
        $this->earnHelper   = $earnHelper;
    }

    /**
     * @param int $points
     * @param \Magento\Catalog\Model\Product $product $product
     * @return int
     */
    public function getProductPointsAsMoney($points, $product)
    {
        return $this->spendHelper
            ->getProductPointsAsMoney(
                $points,
                $this->storeManager->getStore()->getWebsiteId(),
                $this->customerSession->getCustomerGroupId(),
                $this->earnHelper->getProductPriceByProduct($product), //@dva unclear
                $this->customerSession->getCustomer()
            );
    }

}