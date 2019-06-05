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

use Magento\Framework\Registry;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Bundle\Model\Product\Type;
use Mirasvit\Rewards\Helper\Balance\Earn;
use Mirasvit\Rewards\Model\Config;

class BundlePriceConfig implements ObserverInterface
{
    private $registry;
    private $config;
    private $productFactory;
    private $earnHelper;
    private $earnOutput;
    private $helper;
    private $storeManager;
    private $customerSession;

    /**
     * @var array
     */
    private $prodConfig = [];

    public function __construct(
        Registry $registry,
        Config $config,
        ProductFactory $productFactory,
        Earn $earnHelper,
        \Mirasvit\Rewards\Helper\Output\Earn $earnOutput,
        \Mirasvit\Rewards\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->registry        = $registry;
        $this->config          = $config;
        $this->productFactory  = $productFactory;
        $this->earnHelper      = $earnHelper;
        $this->earnOutput      = $earnOutput;
        $this->helper          = $helper;
        $this->storeManager    = $storeManager;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        /** @var \Magento\Framework\DataObject $configObj */
        $configObj = $observer->getEvent()->getData('configObj');
        if ($this->prodConfig) { // some third-party extensions call this event twice
            return;
        }
        if (!$this->config->getDisplayOptionsIsShowPointsOnProductPage()) {
            return;
        }
        $this->prodConfig = $configObj->getConfig();

        if ($this->isBundle()) {
            if ($this->isRules()) {
                $this->getBundleOptions();
            }
        } else {
            $this->getCustomOptions();
        }

        $configObj->setConfig($this->prodConfig);
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }

    /**
     * @return bool
     */
    private function isBundle()
    {
        if ($this->registry->registry('current_product')) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->registry->registry('current_product');

            return $product->getTypeId() == Type::TYPE_CODE;
        } else {
            return isset($this->prodConfig['options']);
        }
    }

    /**
     * @return bool
     */
    private function isRules()
    {
        return (bool)$this->earnHelper->getProductRules()->count();
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function getBundleOptions()
    {
        $config = $this->prodConfig;
        $config['rewardLabel'] = $this->helper->getPointsName();
        $config['rounding']    = $this->config->getAdvancedEarningRoundingStype();
        if (isset($config['options'])) {
            foreach ($config['options'] as $selectionId => $option) {
                foreach ($option['selections'] as $optionId => $data) {
                    $productId = $data['optionId'];
                    $product   = $this->productFactory->create()->loadByAttribute('entity_id', $productId);
                    $product->setOptionId($data['optionId']);
                    $product->setSelectionId($selectionId);
                    $product->setSelectionQty($data['qty']);
                    $rules     = $this->earnHelper->getProductRulesCoefficient($product);
                    $points    = $this->earnOutput->getProductPoints($product);
                    $config['options'][$selectionId]['selections'][$optionId]['rewardRules'] = $rules;
                    if ($points) {
                        $config['options'][$selectionId]['selections'][$optionId]['prices']['Rewards']['amount'] =
                            $points;
                    }
                }
            }
        }
        $bundleProduct = $this->registry->registry('current_product');
        $config["baseProductPoints"] = $this->earnHelper->getRoundingProductPoints(
            $bundleProduct,
            $this->customerSession->getCustomerGroupId(),
            $this->storeManager->getStore()->getWebsiteId(),
            0 //because it's base bundle. price is sum of its subproducts.
        );
        $this->prodConfig = $config;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function getCustomOptions()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->registry->registry('current_product');
        if ($product) {
            $config = $this->prodConfig;
            foreach ($config as $optionId => $data) {
                $rules = $this->earnHelper->getProductRulesCoefficient($product->getId());
                $config[$optionId]['prices']['rewardRules'] = $rules;
            }

            $config['rewardLabel'] = $this->config->getGeneralPointUnitName();
            $config['rounding'] = $this->config->getAdvancedEarningRoundingStype();
            $this->prodConfig = $config;

            $this->getOptionsPoints();
        }
    }

    /**
     *
     *
     * @return void
     */
    private function getOptionsPoints()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->registry->registry('current_product');
        $config = $this->prodConfig;

        $options = $product->getOptions();
        if (!$options) {
            return;
        }
        foreach ($options as $option) {
            if ($option->hasValues()) {
                if (isset($config[$option->getOptionId()]['prices']['rewardRules'][$product->getId()])) {
                    $rules = $config[$option->getOptionId()]['prices']['rewardRules'][$product->getId()];
                } else {
                    continue;
                }
                foreach ($option->getValues() as $valueId => $value) {
                    foreach ($rules as $ruleId => $ruleOptions) {
                        $valuePrice = $config[$option->getOptionId()][$valueId]['prices']['finalPrice']['amount'];
                        $points = $valuePrice / $ruleOptions['coefficient'];
                        if (isset($config[$option->getOptionId()][$valueId]['prices']['rewardRules']['amount'])) {
                            $config[$option->getOptionId()][$valueId]['prices']['rewardRules']['amount'] += $points;
                        } else {
                            $config[$option->getOptionId()][$valueId]['prices']['rewardRules'] = ['amount' => $points];
                        }
                    }
                }
            }
        }
        $this->prodConfig = $config;
    }
}
