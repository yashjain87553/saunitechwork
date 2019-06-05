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


namespace Mirasvit\Rewards\Plugin\Swatches\Block\Product\Renderer\Listing\Configurable;

use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Block\Product\Renderer\Listing\Configurable as ListingBlock;
use Mirasvit\Rewards\Helper\Json;
use Mirasvit\Rewards\Helper\Balance\Earn as EarnHelper;
use Mirasvit\Rewards\Helper\Output\Earn;
use Mirasvit\Rewards\Model\Config;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class AddPointsDataPlugin
{
    private $customerSession;
    private $storeManager;
    private $earnHelper;
    private $jsonHelper;
    private $config;
    private $earnOutput;

    public function __construct(
        Session $customerSession,
        StoreManagerInterface $storeManager,
        EarnHelper $earnHelper,
        Json $jsonHelper,
        Config $config,
        Earn $earnOutput
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager    = $storeManager;
        $this->earnHelper      = $earnHelper;
        $this->jsonHelper      = $jsonHelper;
        $this->config          = $config;
        $this->earnOutput      = $earnOutput;
    }

    /**
     * @param ListingBlock $listingBlock
     * @param \callable    $proceed
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function aroundGetPricesJson(ListingBlock $listingBlock, $proceed)
    {
        \Magento\Framework\Profiler::start(__CLASS__.'_default:'.__METHOD__);
        $returnValue = $proceed();

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $productRules = $this->earnHelper->getProductRules($websiteId, $customerGroupId);
        if (!$productRules->count()) {
            return $returnValue;
        }

        $product = $listingBlock->getProduct();
        if (!$product || !$product->getId() || !$this->config->getDisplayOptionsIsShowPointsOnFrontend()) {
            \Magento\Framework\Profiler::stop(__CLASS__.'_default:'.__METHOD__);
            return $returnValue;
        }

        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $data = $this->jsonHelper->unserialize($returnValue);

        $points = $this->earnOutput->getProductFloatPoints($product);
        $data['rewardRules'] = ['amount' => $points];

        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
        \Magento\Framework\Profiler::stop(__CLASS__.'_default:'.__METHOD__);

        return $this->jsonHelper->serialize($data);
    }
}