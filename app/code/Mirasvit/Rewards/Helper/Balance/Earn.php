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



namespace Mirasvit\Rewards\Helper\Balance;

use Mirasvit\Rewards\Api\Data\Earning\RuleInterface;
use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Helper\Product\Bundle as BundleHelper;
use Mirasvit\Rewards\Model\Config as Config;
use Mirasvit\Rewards\Service\Product\Bundle\CalcPriceService as BundleCalcPriceService;
use Mirasvit\Rewards\Service\Product\CalcPriceService;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Registry;

/**
 * Main place to calculate earning points
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Earn extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PRICE = 'price';
    const PRICE_WITH_TAX = 'tax_price';

    private $productMessages = [];
    private $rules = [];

    private $bundleHelper;
    private $bundleCalcPriceService;
    private $registry;
    private $stockRegistry;
    private $cartFactory;
    private $calcPriceService;
    private $productFactory;
    private $lowestPriceOptionsProvider;
    private $earningRuleCollectionFactory;
    private $config;
    private $catalogData;
    private $storeManager;
    private $customerFactory;
    private $customerSession;
    private $taxConfig;
    private $productMetadata;
    private $context;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        BundleCalcPriceService $bundleCalcPriceService,
        BundleHelper $bundleHelper,
        Registry $registry,
        StockRegistryInterface $stockRegistry,
        CalcPriceService $calcPriceService,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory $earningRuleCollectionFactory,
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->bundleHelper                 = $bundleHelper;
        $this->bundleCalcPriceService       = $bundleCalcPriceService;
        $this->registry                     = $registry;
        $this->stockRegistry                = $stockRegistry;
        $this->cartFactory                  = $cartFactory;
        $this->calcPriceService             = $calcPriceService;
        $this->productFactory               = $productFactory;
        $this->earningRuleCollectionFactory = $earningRuleCollectionFactory;
        $this->config                       = $config;
        $this->catalogData                  = $catalogData;
        $this->storeManager                 = $storeManager;
        $this->customerFactory              = $customerFactory;
        $this->customerSession              = $customerSession;
        $this->taxConfig                    = $taxConfig;
        $this->productMetadata              = $productMetadata;
        $this->context                      = $context;

        $interface = 'Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface';
        if (interface_exists($interface)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->lowestPriceOptionsProvider = $objectManager->create($interface);
        }

        parent::__construct($context);
    }

    /**
     * @return \Mirasvit\Rewards\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Round points depends on earning rounding style in config
     * @param float $points
     * @return float
     */
    public function roundPoints($points)
    {
        $points = (string)$points; // some integer numbers of type float after conversion change its values
        if ($this->getConfig()->getAdvancedEarningRoundingStype()) {
            return floor($points);
        } else {
            return ceil($points);
        }
    }

    /**
     * Check if rewards config allows to include tax in earning amount
     * @return bool
     */
    public function isIncludeTax()
    {
        return $this->getConfig()->getGeneralIsIncludeTaxEarning();
    }

    /**
     * Calc cart subtotal for one rule
     * @param \Magento\Quote\Model\Quote           $quote
     * @param \Mirasvit\Rewards\Model\Earning\Rule $rule
     *
     * @return float
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getLimitedSubtotal($quote, $rule)
    {
        $priceIncludesTax = $this->isIncludeTax($quote);

        $subtotal = 0;
        foreach ($quote->getItemsCollection() as $item) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            if ($item->getParentItemId()) {
                continue;
            }
            if ($rule->getActions()->validate($item)) {
                $subtotal += $this->getProductPriceByItem($item);
            }
        }

        if ($this->getConfig()->getGeneralIsEarnShipping() && !$quote->isVirtual()) {
            if ($priceIncludesTax) {
                $shipping = $quote->getShippingAddress()->getBaseShippingInclTax();
            } else {
                $shipping = $quote->getShippingAddress()->getBaseShippingInclTax() -
                    $quote->getShippingAddress()->getBaseShippingTaxAmount();
            }

            $subtotal += $shipping;
        }

        if ($this->context->getModuleManager()->isEnabled('Mirasvit_Credit')) {
            if ($credit = $quote->getShippingAddress()->getBaseCreditAmount()) {
                $subtotal -= $credit;
            }
        }

        if ($subtotal < 0) {
            $subtotal = 0;
        }

        return $subtotal;
    }

    /**
     * Calc sum of earned points for for Product and Cart Rules
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return int number of points
     */
    public function getPointsEarned($quote)
    {
        $totalPoints = 0;
        foreach ($quote->getAllItems() as $item) {
            $productId = $item->getProductId();
            $product = $this->productFactory->create()->load($productId);

            if ($item->getParentItemId() && $product->getTypeID() == 'simple') {
                continue;
            }

            $productPoints = $this->getProductPoints(
                    $product,
                    $quote->getCustomerGroupId(),
                    $quote->getStore()->getWebsiteId(),
                    $this->getProductPriceByItem($item)
                );

            $totalPoints += $productPoints;
        }
        if ($totalPoints) {
            $priceIncludesTax = $this->isIncludeTax($quote);
            if ($this->getConfig()->getGeneralIsEarnShipping()) {
                if ($priceIncludesTax) {
                    $shipping = $quote->getShippingAddress()->getBaseShippingInclTax();
                } else {
                    $shipping = $quote->getShippingAddress()->getBaseShippingInclTax() -
                        $quote->getShippingAddress()->getBaseShippingTaxAmount();
                }

                $totalPoints += $this->getShippingPoints(
                    $shipping,
                    $quote->getCustomerGroupId(),
                    $quote->getStore()->getWebsiteId()
                );
            }
        }

        $totalPoints += $this->getCartPoints($quote);

        return $this->roundPoints($totalPoints);
    }

    /**
     * Function returns true for grouped or bundled products if after adding to the cart
     * customer may receive product points
     *
     * @param \Magento\Catalog\Model\Product       $product
     * @param int                             $customerGroupId
     * @param int                             $websiteId
     * @return bool|true
     */
    public function getIsProductPointsPossible($product, $customerGroupId, $websiteId)
    {
        if (!$product) {
            return false;
        }

        $possibleNotstandardProducts = [
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
            \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE,
        ];

        if (!in_array($product->getTypeId(), $possibleNotstandardProducts)) {
            return false;
        }

        $rules = $this->getProductRules($websiteId, $customerGroupId);

        return $rules->count() > 0;
    }

    /**
     * Calculates the number of earning points for shipping using first valid rule
     *
     * @param float    $shippingPrice
     * @param int|bool $customerGroupId
     * @param int|bool $websiteId
     *
     * @return int number of points
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getShippingPoints(
        $shippingPrice,
        $customerGroupId,
        $websiteId
    ) {
        if (!$this->productMessages) {
            return 0;
        }
        $currentTier = (int)$this->customerSession->getCustomer()->getData(TierInterface::CUSTOMER_KEY_TIER_ID);

        $total = 0;
        $rules = $this->getProductRules($websiteId, $customerGroupId);

        $productRules = reset($this->productMessages);
        $ruleIds = array_keys($productRules);
        $ruleId = reset($ruleIds);

        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        foreach ($rules as $rule) {
            if ($rule->getId() != $ruleId) {
                continue;
            }
            $tears = $rule->getTiersSerialized();
            if ($currentTier) {
                if (isset($tears[$currentTier])) {
                    $tierData = $tears[$currentTier];
                } else {
                    $tierData = $rule->getDefaultTierData();
                }
            } else {
                $tierData = array_shift($tears);
            }
            $rule->afterLoad();
            if ($tierData[RuleInterface::KEY_TIER_KEY_EARNING_STYLE] == Config::EARNING_STYLE_AMOUNT_PRICE) {
                $total = $shippingPrice / $tierData[RuleInterface::KEY_TIER_KEY_MONETARY_STEP] *
                    $tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS];
            }
        }

        return $total;
    }

    /**
     * Calculates the number of points for some product.
     *
     * @param \Magento\Catalog\Model\Product       $product
     * @param int|bool                             $customerGroupId
     * @param int|bool                             $websiteId
     * @param string                         $price
     *
     * @return int number of points
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPoints(
        $product,
        $customerGroupId,
        $websiteId,
        $price
    ) {
        if (!($product instanceof \Magento\Catalog\Model\Product)) {
            $product = $this->productFactory->create()->load($product->getId());
        }
        $product->setCustomer($this->customerSession->getCustomer());
        $currentTier = (int)$this->customerSession->getCustomer()->getData(TierInterface::CUSTOMER_KEY_TIER_ID);

        $websiteId = $this->storeManager->getWebsite()->getId();
        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        $minAllowed = max((float)$stockItem->getMinSaleQty(), 1);
        $rulePrice = $minAllowed * $price;

        $total = 0;
        $rules = $this->getProductRules($websiteId, $customerGroupId);
        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        foreach ($rules as $rule) {
            $tears = $rule->getTiersSerialized();
            if ($currentTier) {
                if (isset($tears[$currentTier])) {
                    $tierData = $tears[$currentTier];
                } else {
                    $tierData = $rule->getDefaultTierData();
                }
            } else {
                $tierData = array_shift($tears);
            }
            $rule->afterLoad();
            if ($tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS] &&
                $rulePrice >= $tierData[RuleInterface::KEY_TIER_KEY_MONETARY_STEP] &&
                $rule->validate($product)
            ) {
                switch ($tierData[RuleInterface::KEY_TIER_KEY_EARNING_STYLE]) {
                    case Config::EARNING_STYLE_GIVE:
                        $total += $tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS];
                        break;

                    case Config::EARNING_STYLE_AMOUNT_PRICE:
                        $amount    = $price / $tierData[RuleInterface::KEY_TIER_KEY_MONETARY_STEP] *
                            $tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS];
                        if (
                            $tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT] &&
                            $amount > $tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT]
                        ) {
                            $amount = $tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT];
                        }
                        $total += $amount;
                        if ($rule->getProductNotification()) {
                            $this->productMessages[$product->getId()][$rule->getId()] = $rule->getProductNotification();
                        }
                        break;
                }

                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }

        return $total;
    }

    /**
     * Calculates the number of points for the product.
     *
     * @api
     * @todo merge with getProductPoints
     *
     * @param \Magento\Catalog\Model\Product               $product
     * @param float                                        $price
     * @param int                                          $tierId
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int|bool                                     $customerGroupId
     * @param int|bool                                     $websiteId
     *
     * @return int number of points
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPointsByTier(
        $product,
        $price,
        $tierId,
        $customer = null,
        $customerGroupId = 0,
        $websiteId = 1
    ) {
        if (!($product instanceof \Magento\Catalog\Model\Product)) {
            $product = $this->productFactory->create()->load($product->getId());
        }
        $product->setCustomer($customer);
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $websiteId);
        $minAllowed = max((float)$stockItem->getMinSaleQty(), 1);
        $rulePrice = $minAllowed * $price;

        $total = 0;
        $rules = $this->getProductRules($websiteId, $customerGroupId);
        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        foreach ($rules as $rule) {
            $tears = $rule->getTiersSerialized();
            if ($tierId) {
                if (isset($tears[$tierId])) {
                    $tierData = $tears[$tierId];
                } else {
                    $tierData = $rule->getDefaultTierData();
                }
            } else {
                $tierData = array_shift($tears);
            }
            $rule->afterLoad();
            if ($tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS] &&
                $rulePrice >= $tierData[RuleInterface::KEY_TIER_KEY_MONETARY_STEP] &&
                $rule->validate($product)
            ) {
                switch ($tierData[RuleInterface::KEY_TIER_KEY_EARNING_STYLE]) {
                    case Config::EARNING_STYLE_GIVE:
                        $total += $tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS];
                        break;

                    case Config::EARNING_STYLE_AMOUNT_PRICE:
                        $amount    = $price / $tierData[RuleInterface::KEY_TIER_KEY_MONETARY_STEP] *
                            $tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS];
                        if (
                            $tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT] &&
                            $amount > $tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT]
                        ) {
                            $amount = $tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT];
                        }
                        $total += $amount;
                        break;
                }
                if ($rule->getProductNotification()) {
                    $this->productMessages[$product->getId()][$rule->getId()] = $rule->getProductNotification();
                }

                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }

        return $total;
    }

    /**
     * Calculates nd round the number of points for some product.
     *
     * @param \Magento\Catalog\Model\Product       $product
     * @param int                             $customerGroupId
     * @param int                             $websiteId
     * @param float                          $price
     *
     * @return int number of points
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getRoundingProductPoints(
        \Magento\Catalog\Model\Product $product,
        $customerGroupId,
        $websiteId,
        $price
    ) {
        return $this->roundPoints(
            $this->getProductPoints($product, $customerGroupId, $websiteId, $price)
        );
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getProductMessages($productId)
    {
        return isset($this->productMessages[$productId]) ? $this->productMessages[$productId] : [];
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    private function isBundle($product)
    {
        return $product->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    private function isConfigurable($product)
    {
        return $product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
    }

    /**
     * Get bundle product price in base currency depends on price type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string                         $priceType
     *
     * @return float
     */
    private function getBundlePrice($product, $priceType)
    {
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);

        /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionCollection */
        $optionCollection = $typeInstance->getOptionsCollection($product);

        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product),
            $product
        );

        $options = $optionCollection->appendSelections($selectionCollection, true, false);
        $bundlePrice = 0;
        if ($priceType == 'minPrice') {
            $bundlePrice = $this->bundleHelper->getMinOptionsPrice($product, $options);
        } elseif ($priceType == 'maxPrice') {
            $bundlePrice = $this->bundleHelper->getMaxOptionsPrice($product, $options);
        } elseif ($priceType == 'configured_price') {
            $bundlePrice = $this->bundleHelper->getConfiguredOptionsPrice($options);
        }

        return $bundlePrice;
    }

    /**
     * Check if product price contains tax
     * @return bool
     */
    private function isUseTax()
    {
        return $this->taxConfig->getPriceDisplayType() !== 1 && $this->getConfig()->getGeneralIsIncludeTaxEarning();
    }

    /**
     * Price with/without tax. Depends on settings of rwp.
     * Used to calculate points.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string|int|null $priceId
     *
     * @return float
     */
    public function getProductPriceByProduct(
        \Magento\Catalog\Model\Product $product, $priceType = 'final_price'
    ) {
        $bundleProduct = $this->registry->registry('current_product');
        if ($this->isBundle($product)) {
            $price = $this->getBundlePrice($product, $priceType);
        } elseif ($bundleProduct && $this->isBundle($bundleProduct) && $bundleProduct->getId() != $product->getId()) {
            // bundle options
            $price = $this->bundleCalcPriceService->getOptionPrice($bundleProduct, $product);
        } elseif ($this->isConfigurable($product)) {
            $price = $product->getPriceModel()->getBasePrice($product);
            if ($this->lowestPriceOptionsProvider) {
                foreach ($this->lowestPriceOptionsProvider->getProducts($product) as $subProduct) {
                    $price            = $subProduct->getPriceModel()->getBasePrice($subProduct);
                    $catalogRulePrice = $this->calcPriceService->getBaseCatalogPriceRulePrice($subProduct);
                    if ($catalogRulePrice && $catalogRulePrice < $price) {
                        $price = $catalogRulePrice;
                    }
                }
            }
            if ($this->isUseTax()) {
                $price = $this->catalogData->getTaxPrice($product, $price, true);
            }
        } else {
            $price = $product->getPriceModel()->getBasePrice($product);
            $catalogRulePrice = $this->calcPriceService->getBaseCatalogPriceRulePrice($product);
            if ($catalogRulePrice && $catalogRulePrice < $price) {
                $price = $catalogRulePrice;
            }
            if (!$price) {
                $price = $product->getPriceInfo()->getPrice('base_price')->getAmount()->getValue();
            } elseif ($this->isUseTax()) {
                $price = $this->catalogData->getTaxPrice($product, $price, true);
            }
        }

        return $price;
    }

    /**
     * Calcs earned points for Cart rules
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return int number of points
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getCartPoints($quote)
    {
        $total = 0;
        $customerGroupId = $quote->getCustomerGroupId();
        $websiteId = $quote->getStore()->getWebsiteId();
        $rules = $this->earningRuleCollectionFactory->create()
                    ->addWebsiteFilter($websiteId)
                    ->addCustomerGroupFilter($customerGroupId)
                    ->addCurrentFilter()
                    ->addFieldToFilter('type', \Mirasvit\Rewards\Model\Earning\Rule::TYPE_CART);
        $rules->getSelect()->order('sort_order');
        $currentTier = (int)$this->customerSession->getCustomer()->getData(TierInterface::CUSTOMER_KEY_TIER_ID);

        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        foreach ($rules as $rule) {
            $rule->afterLoad();
            $tears = $rule->getTiersSerialized();
            if ($currentTier) {
                if (isset($tears[$currentTier])) {
                    $tierData = $tears[$currentTier];
                } else {
                    $tierData = $rule->getDefaultTierData();
                }
            } else {
                $tierData = array_shift($tears);
            }
            /** @var \Magento\Quote\Model\Quote\Address $address */
            if ($quote->isVirtual()) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }

            if (version_compare($this->productMetadata->getVersion(), '2.2.0', '<')) {
                foreach ($address->getAllItems() as $item) {// total_qty - allowed only in total collection process
                    $address->setTotalQty($address->getTotalQty() + $item->getQty());
                }
            }
            $address->setCustomer($this->customerSession->getCustomer());
            if ($rule->validate($address)) {
                switch ($tierData[RuleInterface::KEY_TIER_KEY_EARNING_STYLE]) {
                    case Config::EARNING_STYLE_GIVE:
                        $total += $tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS];
                        break;

                    case Config::EARNING_STYLE_AMOUNT_SPENT:
                        $subtotal = $this->getLimitedSubtotal($quote, $rule);
                        $steps = $subtotal / $tierData[RuleInterface::KEY_TIER_KEY_MONETARY_STEP];
                        $amount = $steps * $tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS];
                        if ($tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT] &&
                            $amount > $tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT]
                        ) {
                            $amount = $tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT];
                        }
                        $total += $amount;
                        break;
                    case Config::EARNING_STYLE_QTY_SPENT:
                        $qty = 0;
                        foreach ($quote->getItemsCollection() as $item) {
                            /** @var \Magento\Quote\Model\Quote\Item $item */
                            if ($item->getParentItemId()) {
                                continue;
                            }
                            if ($rule->getActions()->validate($item)) {
                                $qty += $item->getQty();
                            }
                        }
                        $qty = round($qty/$tierData[RuleInterface::KEY_TIER_KEY_QTY_STEP], 0, PHP_ROUND_HALF_DOWN);
                        $amount = $qty * $tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS];
                        if ($tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT] && $amount > $tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT]) {
                            $amount = $tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT];
                        }
                        $total += $amount;
                        break;
                }
                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }

        return $total;
    }

    /**
     * Prepare data for product options
     * @param int   $productId
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getProductRulesCoefficient($productId)
    {
        if (!is_object($productId)) {
            $product = $this->productFactory->create()->load($productId);
        } else {
            $product = $productId;
        }
        $product->setCustomer($this->customerSession->getCustomer());
        $customerGroupId = 0 ; //NOT LOGGED IN
        if ($id = $this->customerSession->getCustomerId()) {
            $customerGroupId = $this->customerFactory->create()
                ->load($id)
                ->getGroupId();
        }
        $currentTier = (int)$this->customerSession->getCustomer()->getData(TierInterface::CUSTOMER_KEY_TIER_ID);

        $websiteId = $this->storeManager->getWebsite()->getId();

        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        $minAllowed = max((float)$stockItem->getMinSaleQty(), 1);

        $data = [];
        $rules = $this->getProductRules($websiteId, $customerGroupId);
        foreach ($rules as $rule) {
            $tears = $rule->getTiersSerialized();
            if ($currentTier) {
                if (isset($tears[$currentTier])) {
                    $tierData = $tears[$currentTier];
                } else {
                    $tierData = $rule->getDefaultTierData();
                }
            } else {
                $tierData = array_shift($tears);
            }
            $rule->afterLoad();
            if ($tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS] &&
                $rule->validate($product)
            ) {
                switch ($tierData[RuleInterface::KEY_TIER_KEY_EARNING_STYLE]) {
                    case Config::EARNING_STYLE_GIVE:
                        $data[$product->getId()][$rule->getId()] = [
                                'points'      => $tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS],
                                'coefficient' => 0,
                            ];
                        break;

                    case Config::EARNING_STYLE_AMOUNT_PRICE:
                        $data[$product->getId()][$rule->getId()] = [
                            'points'       => 0,
                            'rewardsPrice' => $this->getProductPriceByProduct($product),
                            'coefficient'  => $tierData[RuleInterface::KEY_TIER_KEY_MONETARY_STEP] /
                                $tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS],
                            'options'      => [
                                'limit' => (int)$tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT],
                            ],
                        ];
                        break;
                }

                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }
        if ($data) {
            $data['minAllowed'] = $minAllowed;
            $data['rounding']   = $this->config->getAdvancedEarningRoundingStype();
        }

        return $data;
    }

    /**
     * Get product price by quote item
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float
     */
    public function getProductPriceByItem($item)
    {
        $earnIncludingTax = $this->isIncludeTax();
        $store = $this->storeManager->getStore();
        $price = $item->getBasePrice() * $item->getQty();
        //if option "Apply Customer Tax" set to "After Discount"
        if ($this->taxConfig->applyTaxAfterDiscount() && !$this->getConfig()->getGeneralIsIncludeDiscountEarning()) {
            $price -= $item->getBaseDiscountAmount();
        }
        if ($price < 0) {
            $price = 0;
        }
        $store->setCalculateRewardsTax(1);
        $priceIncludesTax = $this->taxConfig->priceIncludesTax();
        if ($priceIncludesTax || (!$this->taxConfig->applyTaxAfterDiscount() && $earnIncludingTax)) {
            $price = $item->getBasePriceInclTax() * $item->getQty();
        } else {
            $price = $this->catalogData
                ->getTaxPrice($item->getProduct(), $price, ($priceIncludesTax || $earnIncludingTax),
                    null, null, null, $store, false);
        }

        //if option "Apply Customer Tax" set to "Before Discount"
        if (!$this->taxConfig->applyTaxAfterDiscount() && !$this->getConfig()->getGeneralIsIncludeDiscountEarning()) {
            $price -= $item->getBaseDiscountAmount();
        }
        $price -= $item->getBaseRewardsDiscountAmount();
        if ($price < 0) {
            $price = 0;
        }
        $store->setCalculateRewardsTax(0);
        if ($priceIncludesTax) {
            $price += (float)$item->getWeeeTaxAppliedAmountInclTax() * $item->getQty();
        }

        return $price;
    }

    /**
     * Prepare earning Product rules
     * @param int $websiteId
     * @param int $customerGroupId
     * @return \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\Collection
     */
    public function getProductRules($websiteId = false, $customerGroupId = 0)
    {
        if ($id = $this->customerSession->getCustomerId()) {
            $customerGroupId = $this->customerFactory->create()
                ->load($id)
                ->getGroupId();
        }
        if (empty($this->rules[$websiteId][$customerGroupId])) {
            if ($websiteId === false) {
                $websiteId = $this->storeManager->getWebsite()->getId();
            }
            $rules = $this->earningRuleCollectionFactory->create()
                ->addWebsiteFilter($websiteId)
                ->addCustomerGroupFilter($customerGroupId)
                ->addCurrentFilter()
                ->addFieldToFilter('type', \Mirasvit\Rewards\Model\Earning\Rule::TYPE_PRODUCT);
            $rules->getSelect()->order('sort_order');

            $this->rules[$websiteId][$customerGroupId] = $rules;
        }

        return $this->rules[$websiteId][$customerGroupId];
    }
}
