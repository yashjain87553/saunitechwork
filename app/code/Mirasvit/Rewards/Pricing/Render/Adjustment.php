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


namespace Mirasvit\Rewards\Pricing\Render;

use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\AbstractAdjustment;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Weee\Helper\Data as WeeData;

use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Helper\Data;
use Mirasvit\Rewards\Helper\Balance\Earn as EarnHelper;
use Mirasvit\Rewards\Helper\Output\Spend;
use Mirasvit\Rewards\Helper\Output\Earn;
use Mirasvit\Rewards\Service\Currency\Calculation;
use Mirasvit\Rewards\Service\Product\Bundle\CalcPriceService as BundleCalcPriceService;
use Mirasvit\Rewards\Service\Product\CalcPriceService;

/**
 * Display points on product and category pages
 *
 * @method string getIdSuffix()
 * @method string getDisplayLabel()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Adjustment extends AbstractAdjustment
{
    private $productPoints = [];
    private $productPointsLabel = [];

    private $appState;
    private $bundleCalcPriceService;
    private $catalogData;
    private $calcPriceService;
    private $calculation;
    private $config;
    private $customerSession;
    private $earnHelper;
    private $earnOutput;
    private $registry;
    private $rewardsDataHelper;
    private $spendOutput;
    private $storeManager;
    private $weeData;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Config                 $config,
        Earn                   $earnOutput,
        Spend                  $spendOutput,
        Data                   $rewardsDataHelper,
        EarnHelper             $earnHelper,
        CatalogData            $catalogData,
        Registry               $registry,
        WeeData                $weeData,
        Session                $customerSession,
        PriceCurrencyInterface $priceCurrency,
        BundleCalcPriceService $bundleCalcPriceService,
        CalcPriceService       $calcPriceService,
        Calculation            $calculation,
        Template\Context       $context,
        array                  $data = []
    ) {
        $this->earnOutput             = $earnOutput;
        $this->spendOutput            = $spendOutput;
        $this->rewardsDataHelper      = $rewardsDataHelper;
        $this->earnHelper             = $earnHelper;
        $this->catalogData            = $catalogData;
        $this->registry               = $registry;
        $this->weeData                = $weeData;
        $this->customerSession        = $customerSession;
        $this->calculation            = $calculation;
        $this->config                 = $config;
        $this->calcPriceService       = $calcPriceService;
        $this->bundleCalcPriceService = $bundleCalcPriceService;
        $this->storeManager           = $context->getStoreManager();
        $this->appState               = $context->getAppState();

        parent::__construct($context, $priceCurrency, $data);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProduct()
    {
        return $this->getSaleableItem();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * {@inheritdoc}
     */
    protected function apply()
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $productRules = $this->earnHelper->getProductRules($websiteId, $customerGroupId);
        if (!$productRules->count()) {
            return '';
        }

        if ($this->isProductPage() && !$this->config->getDisplayOptionsIsShowPointsOnProductPage()) {
            return '';
        }
        if (!$this->isProductPage() && !$this->config->getDisplayOptionsIsShowPointsOnFrontend()) {
            return '';
        }

        return $this->toHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function getAdjustmentCode()
    {
        return \Mirasvit\Rewards\Pricing\Adjustment::ADJUSTMENT_CODE;
    }

    /**
     * Define if both prices should be displayed
     *
     * @return bool
     */
    public function isShowPoints()
    {
        \Magento\Framework\Profiler::start(__METHOD__);
        if ($this->isProductPage()) {
            $isAllowToShow = $this->config->getDisplayOptionsIsShowPointsOnProductPage();
        } else {
            $isAllowToShow = $this->config->getDisplayOptionsIsShowPointsOnFrontend();
        }

        $f = $isAllowToShow && !$this->isOptionPrice();
        \Magento\Framework\Profiler::stop(__METHOD__);
        return $f;
    }

    /**
     * @return bool
     */
    public function isOptionPrice()
    {
        return $this->getAmountRender()->getPrice()->getPriceCode() == CustomOptionPrice::PRICE_CODE;
    }

    /**
     * @return int
     */
    public function getCurrentPoints()
    {
        $n = $this->earnOutput->getProductPoints($this->getProduct());
        return $n;
    }

    /**
     * @return float
     */
    public function getCurrentFloatPoints()
    {
        $n = $this->earnOutput->getProductFloatPoints($this->getProduct());
        return $n;
    }

    /**
     * @return float
     */
    public function getPointsRounding()
    {
        return (int)$this->config->getAdvancedEarningRoundingStype();
    }

    /**
     * @return bool
     */
    private function isBundle()
    {
        return $this->getSaleableItem()->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE;
    }

    /**
     * @return int
     */
    public function getPoints()
    {
        \Magento\Framework\Profiler::start(__METHOD__);

        if (isset($this->productPoints[$this->getProduct()->getId()])) {
            \Magento\Framework\Profiler::stop(__METHOD__);
            return $this->productPoints[$this->getProduct()->getId()];
        }

        $priceType = $this->getAmountRender()->getPrice()->getPriceCode();
        $price = null;
        if ($this->isBundle() && (
                $this->getAmountRender()->getPriceType() == 'minPrice' ||
                $this->getAmountRender()->getPriceType() == 'maxPrice'
            )
        ) {
            $priceType = $this->getAmountRender()->getPriceType();
        } elseif ($this->isBundle()) {
            /** @var \Magento\Bundle\Pricing\Price\BundleRegularPrice $regularPriceModel */
            $price = $this->bundleCalcPriceService->getDisplayPrice($this->getProduct());
        } elseif ($priceType == 'bundle_option') {
            $bundleProduct = $this->registry->registry('current_product');
            // bundle options
            $price = $this->bundleCalcPriceService->getOptionPrice($bundleProduct, $this->getProduct());
        } else {
            $prices = [];
            $specialPriceObject = $this->getProduct()->getPriceInfo()->getPrice('special_price');
            if ($specialPriceObject->getValue() && $specialPriceObject->isScopeDateInInterval()) {
                $prices[] = $this->getProduct()->getSpecialPrice();
            }
            $catalogPrice = $this->calcPriceService->getBaseCatalogPriceRulePrice($this->getProduct());
            if ($catalogPrice) {
                $prices[] = $catalogPrice;
            }
            $price = $this->getProduct()->getPriceModel()
                ->getTierPrice($this->getProduct()->getQty() ?: 1, $this->getProduct());
            if ($price) {
                $prices[] = $price;
            }
            $price = $prices ? min($prices) : 0;
        }
        $points = $this->earnOutput->getProductPoints($this->getProduct(), $priceType, $price);
        \Magento\Framework\Profiler::stop(__METHOD__);

        return $points;
    }

    /**
     * @return string
     */
    public function getPointsFormatted()
    {
        \Magento\Framework\Profiler::start(__METHOD__);

        if (isset($this->productPointsLabel[$this->getProduct()->getId()])) {
            \Magento\Framework\Profiler::stop(__METHOD__);
            return $this->productPointsLabel[$this->getProduct()->getId()];
        }

        $points = $this->getPoints();

        if ($this->config->getGeneralIsDisplayProductPointsAsMoney()) {
            $money = $this->spendOutput->getProductPointsAsMoney($points, $this->getProduct());
            if ($points != $money) {
                \Magento\Framework\Profiler::stop(__METHOD__);

                $label                                             = __("Possible discount %1 %2", $this->getLabel(), $money);
                $this->productPoints[$this->getProduct()->getId()] = $label;

                return $label;
            }
        }

        $label = __('Earn %1 %2', $this->getLabel(), $this->rewardsDataHelper->formatPoints($points));
        if ($this->isBundle()) {
            $label = __('Earn at least %1 %2', $this->getLabel(), $this->rewardsDataHelper->formatPoints($points));
        }

        \Magento\Framework\Profiler::stop(__METHOD__);
        $this->productPointsLabel[$this->getProduct()->getId()] = $label;

        return $label;
    }

    /**
     * Build identifier with prefix
     *
     * @param string $prefix
     * @return string
     */
    public function buildIdWithPrefix($prefix)
    {
        $priceId = $this->getPriceId();
        if (!$priceId) {
            $priceId = $this->getSaleableItem()->getId();
        }
        return $prefix . $priceId . $this->getIdSuffix();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        $label = '';
        if (!$this->getAmountRender()) {
            return $label;
        }

        switch ($this->getAmountRender()->getTypeId()) {
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                $label = 'starting at';
                break;
            case \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE:
                $label = 'Up to';
                break;
        }

        return $label;
    }

    /**
     * @return bool
     */
    public function isFront()
    {
        return $this->appState->getAreaCode() == 'frontend';
    }

    /**
     * @return bool
     */
    public function isProductPage()
    {
//        difference of view. Do not remove commented code!!!
//        if (
//            $this->getCurrentProduct() && $this->getCurrentProduct()->getTypeId() &&
//            (
//                $this->getData('zone') == \Magento\Framework\Pricing\Render::ZONE_ITEM_VIEW ||
//                (// for grouped products zone is inverted
//                    $this->getCurrentProduct()->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE &&
//                    $this->getData('zone') == \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
//                ) ||
//                (// bundle options
//                    $this->getCurrentProduct()->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE &&
//                    $this->getProduct()->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE &&
//                    !$this->getData('zone')
//                )
//            )
//        ) {
//            return true;
//        }

        return $this->getCurrentProduct() && $this->getCurrentProduct()->getTypeId();
    }
}
