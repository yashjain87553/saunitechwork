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

use \Magento\Catalog\Model\Product;

/**
 * Helper class to get earned points. Used on product/category page
 */
class Earn
{
    public function __construct(
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Helper\Balance\Earn $earnHelper,
        \Mirasvit\Rewards\Helper\Balance\Spend $spendHelper,
        \Mirasvit\Rewards\Helper\Data $rewardsDataHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->config            = $config;
        $this->earnHelper        = $earnHelper;
        $this->spendHelper       = $spendHelper;
        $this->rewardsDataHelper = $rewardsDataHelper;
        $this->registry          = $registry;
        $this->customerSession   = $customerSession;
        $this->storeManager      = $storeManager;
    }

    /**
     * Return amount of earned point for the product.
     *
     * @param Product $product
     * @param string $priceType
     * @param float|null $price
     * @return int
     */
    public function getProductPoints(Product $product, $priceType = 'final_price', $price = null)
    {
        $points = 0;
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $productRules = $this->earnHelper->getProductRules($websiteId, $customerGroupId);
        if ($productRules->count()) {
            $points = $this->earnHelper->getRoundingProductPoints(
                $product,
                $customerGroupId,
                $websiteId,
                $price ? : $this->earnHelper->getProductPriceByProduct($product, $priceType)
            );
        }

        return $points;
    }

    /**
     * Use only for points calculations.
     * Return exact amount of earned point for the product without rounding.
     *
     * @param Product $product
     * @param string $priceType
     * @param float|null $price
     * @return int
     */
    public function getProductFloatPoints(Product $product, $priceType = 'final_price', $price = null)
    {
        $points = 0;
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $productRules = $this->earnHelper->getProductRules($websiteId, $customerGroupId);
        if ($productRules->count()) {
            $points = $this->earnHelper->getProductPoints(
                $product,
                $customerGroupId,
                $websiteId,
                $price ? : $this->earnHelper->getProductPriceByProduct($product, $priceType)
            );
        }

        return $points;
    }

}