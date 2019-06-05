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


namespace Mirasvit\Rewards\Plugin\Product\Type;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ProductConfigurable;
use Magento\Catalog\Model\ProductFactory;
use Mirasvit\Rewards\Helper\Balance\Earn as EarnHelper;
use Mirasvit\Rewards\Helper\Output\Earn;
use Mirasvit\Rewards\Model\Config;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class Configurable
{
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Config $config,
        EarnHelper $earnHelper,
        Earn $earnOutput,
        ProductFactory $productFactory
    ) {
        $this->registry        = $registry;
        $this->jsonDecoder     = $jsonDecoder;
        $this->jsonEncoder     = $jsonEncoder;
        $this->customerSession = $customerSession;
        $this->storeManager    = $storeManager;
        $this->config          = $config;
        $this->earnHelper      = $earnHelper;
        $this->earnOutput      = $earnOutput;
        $this->productFactory  = $productFactory;
    }

    /**
     * @param ProductConfigurable $configurable
     * @param \callable     $proceed
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function aroundGetJsonConfig(ProductConfigurable $configurable, $proceed)
    {
        $returnValue = $proceed();
        \Magento\Framework\Profiler::start(__CLASS__.'_default:'.__METHOD__);
        // category page
        if (!$this->registry->registry('current_product') &&
            !$this->config->getDisplayOptionsIsShowPointsOnFrontend()
        ) {
            \Magento\Framework\Profiler::stop(__CLASS__.'_default:'.__METHOD__);
            return $returnValue;
        }
        // product page
        if ($this->registry->registry('current_product') &&
            !$this->config->getDisplayOptionsIsShowPointsOnProductPage()
        ) {
            \Magento\Framework\Profiler::stop(__CLASS__.'_default:'.__METHOD__);
            return $returnValue;
        }
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $productRules = $this->earnHelper->getProductRules($websiteId, $customerGroupId);
        if (!$productRules->count()) {
            return $returnValue;
        }

        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $data = $this->jsonDecoder->decode($returnValue);

        foreach ($data['optionPrices'] as $productId => $prices) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productFactory->create()->loadByAttribute('entity_id', $productId);
            if (!$product) {
                continue;
            }
            $points = $this->earnOutput->getProductFloatPoints($product);
            $data['optionPrices'][$productId]['rewardRules']['amount'] = $points;
        }
        if (!empty($data['productId'])) {
            $product = $this->productFactory->create()->loadByAttribute('entity_id', $data['productId']);
            $points = $this->earnOutput->getProductFloatPoints($product);
        }
        $data['prices']['rewardRules'] = ['amount' => $points];
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
        \Magento\Framework\Profiler::stop(__CLASS__.'_default:'.__METHOD__);

        return $this->jsonEncoder->encode($data);
    }
}