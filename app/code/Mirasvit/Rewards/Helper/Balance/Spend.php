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

use Mirasvit\Rewards\Api\Config\Rule\SpendingStyleInterface;

/**
 * Main file to calc/check spend points
 *
 * @package Mirasvit\Rewards\Helper\Balance
 */
class Spend extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $validItems = [];
    private $taxConfig;
    private $currencyHelper;
    private $rewardsBalance;
    private $purchaseHelper;
    private $spendingRuleCollectionFactory;
    private $config;
    private $roundService;
    private $shippingService;
    private $context;

    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Pricing\Helper\Data $currencyHelper,
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\CollectionFactory $spendingRuleCollectionFactory,
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Helper\Purchase $purchaseHelper,
        \Mirasvit\Rewards\Service\RoundService $roundService,
        \Mirasvit\Rewards\Service\ShippingService $shippingService,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->taxConfig                     = $taxConfig;
        $this->currencyHelper                = $currencyHelper;
        $this->rewardsBalance                = $rewardsBalance;
        $this->spendingRuleCollectionFactory = $spendingRuleCollectionFactory;
        $this->config                        = $config;
        $this->purchaseHelper                = $purchaseHelper;
        $this->roundService                  = $roundService;
        $this->shippingService               = $shippingService;
        $this->context                       = $context;

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
     * @return bool
     */
    public function isRewardsIncludeTax()
    {
        return $this->getConfig()->getGeneralIsIncludeTaxSpending();
    }

    /**
     * If tax applied after discount
     *
     * @return bool
     */
    public function isApplyTaxAfterDiscount()
    {
        return $this->taxConfig->applyTaxAfterDiscount();
    }

    /**
     * Get quote subtotal depends on rewards tax settings
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return float
     */
    protected function getQuoteSubtotal($quote)
    {
        $shippingAmount = 0;
        if ($this->isRewardsIncludeTax()) {
            $subtotal = $quote->getBaseGrandTotal();
        } else {
            $subtotal = $quote->getBaseSubtotalWithDiscount();
            if ($this->getConfig()->getGeneralIsSpendShipping() && !$quote->isVirtual()) {
                $shippingAmount = $this->getShippingAmount($quote);
            }
        }
        $subtotal = $this->applyMinOrderAmount($subtotal);
        $subtotal += $shippingAmount;
        if ((!$quote->getAwUseStoreCredit() || $quote->getIncludeSurcharge()) &&
            $quote->getBaseMagecompSurchargeAmount() && $subtotal
        ) {
            $subtotal += $quote->getBaseMagecompSurchargeAmount();
        }
        if ($quote->getAwUseStoreCredit() &&
            !$quote->getIncludeSurcharge() &&
            $subtotal >= $quote->getBaseMagecompSurchargeAmount()
        ) {
            $subtotal -= $quote->getBaseMagecompSurchargeAmount();
        }
        if ($subtotal < 0) { // compatibility with Aheadworks Store Credit
            $subtotal = 0;
        }

        return $subtotal;
    }

    /**
     * @param float $subtotal
     * @return float
     */
    protected function applyMinOrderAmount($subtotal)
    {
        if (!$this->config->isMagentoMinOrderActive()) {
            return $subtotal;
        }

        $subtotal -= $this->config->getMagentoMinOrderAmount();

        return $subtotal;
    }

    /**
     * Calcs quote subtotal for the rule
     * @param \Magento\Quote\Model\Quote            $quote
     * @param \Mirasvit\Rewards\Model\Spending\Rule $rule
     *
     * @return float
     */
    protected function getLimitedSubtotal($quote, $rule)
    {
        $subtotal = 0;
        $priceIncludesTax = $this->isRewardsIncludeTax();
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getItemsCollection() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($rule->getActions()->validate($item)) {
                if ($priceIncludesTax) {
                    $itemPrice = $item->getBasePrice();
                    if ($this->isRewardsIncludeTax() &&
                        ($this->isApplyTaxAfterDiscount() && $itemPrice != $item->getBaseDiscountAmount())
                    ) {
                        $itemPrice += $item->getBaseTaxAmount();
                    }
                    $itemPrice += (float)$item->getWeeeTaxAppliedAmountInclTax();
                    $itemPrice = $itemPrice * $item->getQty() - $item->getBaseDiscountAmount();
                } else {
                    $itemPrice = $item->getBasePrice() * $item->getQty() - $item->getBaseDiscountAmount();
                }
                if ($this->roundService->isFaonniRoundEnabled()) {
                    $subtotal += $this->roundService->faonniRound($itemPrice / $item->getQty()) * $item->getQty();
                } else {
                    $subtotal += $itemPrice;
                }
                if ($itemPrice) {
                    $this->validItems[$rule->getId()][] = $item->getId();
                }
            }
        }
        if ($this->getConfig()->getGeneralIsSpendShipping() && !$quote->isVirtual()) {
            $subtotal += $this->getShippingAmount($quote);
        }
        if ((!$quote->getAwUseStoreCredit() || $quote->getIncludeSurcharge()) &&
            $quote->getBaseMagecompSurchargeAmount() && $subtotal
        ) {
            $subtotal += $quote->getBaseMagecompSurchargeAmount();
        }
        if ($quote->getAwUseStoreCredit() &&
            !$quote->getIncludeSurcharge() &&
            $subtotal >= $quote->getBaseMagecompSurchargeAmount()
        ) {
            $subtotal -= $quote->getBaseMagecompSurchargeAmount();
        }
        $subtotal -= $quote->getBaseAmastyGift();
        if ($subtotal < 0) {
            $subtotal = 0;
        }

        return $subtotal;
    }

    /**
     * Get quote shipping amount depends on rewards ta settings
     * @param \Magento\Quote\Model\Quote $quote
     * @return float
     */
    private function getShippingAmount($quote)
    {
        $shippingAddress = $quote->getShippingAddress();

        if ($quote->getCartShippingMethod()) {
            $shippingAddress->setCollectShippingRates(true)->setShippingMethod(
                $quote->getCartShippingCarrier() . '_' . $quote->getCartShippingMethod()
            );
            $shippingAddress->collectShippingRates();
        }

        return $this->shippingService->getBaseRewardsShippingPrice($shippingAddress);
    }

    /**
     * Get spending rule collection for quote
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\Collection
     */
    protected function getRules($quote)
    {
        $websiteId       = $quote->getStore()->getWebsiteId();
        $customerGroupId = $quote->getCustomerGroupId();

        return $this->getRuleCollection($websiteId, $customerGroupId);
    }

    /**
     * Get spending rule collection for website and customer group
     * @param int $websiteId
     * @param int $customerGroupId
     * @return \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\Collection
     */
    private function getRuleCollection($websiteId, $customerGroupId)
    {
        $rules = $this->spendingRuleCollectionFactory->create()
            ->addWebsiteFilter($websiteId)
            ->addCustomerGroupFilter($customerGroupId)
            ->addCurrentFilter()
        ;
        $rules->getSelect()->order('sort_order ASC');

        return $rules;
    }

    /**
     * Calcs possible earn product points in money equivalent
     * @param int $points
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $subtotal
     * @param \Magento\Customer\Model\Customer $customer
     *
     * @return string
     */
    public function getProductPointsAsMoney($points, $websiteId, $customerGroupId, $subtotal, $customer)
    {
        if ($this->config->getGeneralIsDisplayProductPointsAsMoney()) {
            $rules = $this->getRuleCollection($websiteId, $customerGroupId);
            if ($rules->count()) {
                $pointsMoney = [];
                /** @var \Mirasvit\Rewards\Model\Spending\Rule $rule */
                foreach ($rules as $rule) {
                    $pointsMoney[] = ($points / $rule->getSpendPoints($customer)) *
                        $rule->getMonetaryStep($customer, $subtotal);
                }

                $points = $this->currencyHelper->currency(max($pointsMoney), true, false);
            }
        }

        return $points;
    }

    /**
     * Calcs min and max amount of spend points for quote
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Framework\DataObject
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getCartRange($quote)
    {
        $itemPoints = [];
        $rules = $this->getRules($quote);
        $customer = $quote->getCustomer();
        $balancePoints = $this->rewardsBalance->getBalancePoints($quote->getCustomerId());

        $minPoints = 0;
        $quoteSubTotal = $this->getQuoteSubtotal($quote);
        $totalPoints = 0;
        /** @var \Mirasvit\Rewards\Model\Spending\Rule $rule */
        foreach ($rules as $rule) {
            $rule->afterLoad();
            if ($quote->getItemVirtualQty() > 0) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }
            if (/*$quoteSubTotal > 0 && */$rule->validate($address)) {
                $ruleSubTotal = $this->getLimitedSubtotal($quote, $rule);
                if ($ruleSubTotal > $quoteSubTotal) {
                    $ruleSubTotal = $quoteSubTotal;
                }

                $monetaryStep = $rule->getMonetaryStep($customer, $ruleSubTotal);
                if (!$monetaryStep) {
                    unset($this->validItems[$rule->getId()]);
                    continue;
                }

                $ruleMinPoints   = $rule->getSpendMinAmount($customer, $ruleSubTotal);
                $ruleMaxPoints   = $rule->getSpendMaxAmount($customer, $ruleSubTotal);
                $ruleSpendPoints = $rule->getSpendPoints($customer);
                if (($ruleMinPoints && ($quoteSubTotal / $monetaryStep) < 1) || $ruleMinPoints > $ruleMaxPoints
                    || $ruleMinPoints > $balancePoints) {
                    unset($this->validItems[$rule->getId()]);
                    continue;
                }

                $ruleMinPoints = $ruleMinPoints ? max($ruleMinPoints, $ruleSpendPoints) : $ruleSpendPoints;

                $minPoints = $minPoints ? min($minPoints, $ruleMinPoints) : $ruleMinPoints;

                if ($ruleMinPoints <= $ruleMaxPoints) {
                    $quoteSubTotal -= $ruleMaxPoints / $ruleSpendPoints * $monetaryStep;
                    $totalPoints   += $ruleMaxPoints;
                }

                if ($rule->getSpendingStyle($customer) == SpendingStyleInterface::STYLE_FULL) {
                    $roundedTotalPoints = floor($totalPoints / $ruleSpendPoints) * $ruleSpendPoints;
                    if ($roundedTotalPoints < $totalPoints) {
                        $totalPoints = $roundedTotalPoints + $ruleSpendPoints;
                    } else {
                        $totalPoints = $roundedTotalPoints;
                    }
                    if ($totalPoints > $balancePoints) {
                        $totalPoints = floor($balancePoints / $ruleSpendPoints) * $ruleSpendPoints;
                    }
                }

                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }
        foreach ($this->validItems as $ruleId => $items) {
            $itemPoints = array_merge($itemPoints, $items);
        }
        if ($minPoints > $totalPoints) {
            $minPoints = $totalPoints = 0;
        }

        return new \Magento\Framework\DataObject([
            'min_points' => $minPoints,
            'max_points' => $totalPoints,
            'item_points' => $itemPoints,
        ]);
    }

    /**
     * Check if spend points amount is valid for quote
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $pointsNumber
     * @return \Magento\Framework\DataObject
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getCartPoints($quote, $pointsNumber)
    {
        $customer = $quote->getCustomer();
        $rules = $this->getRules($quote);
        $totalBaseAmount = 0;
        $totalPoints = 0;
        /** @var \Mirasvit\Rewards\Model\Spending\Rule $rule */
        foreach ($rules as $rule) {
            $rule->afterLoad();
            if ($quote->getItemVirtualQty() > 0) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }
            if ($pointsNumber > 0 && $rule->validate($address)) {
                $subtotal         = $this->getLimitedSubtotal($quote, $rule);
                $ruleMaxPoints    = $rule->getSpendMaxAmount($customer, $subtotal);
                $rulePointsNumber = $pointsNumber;
                if ($ruleMaxPoints && $pointsNumber > $ruleMaxPoints) {
                    $rulePointsNumber = $ruleMaxPoints;
                }

                if ($rule->getSpendingStyle($customer) == SpendingStyleInterface::STYLE_PARTIAL) {
                    $stepsSecond = round($rulePointsNumber / $rule->getSpendPoints($customer), 2, PHP_ROUND_HALF_DOWN);
                } else {
                    $spendPoints = $rule->getSpendPoints($customer);
                    $roundedRulePointsNumber = floor(floor($rulePointsNumber / $spendPoints) * $spendPoints);
                    if ($roundedRulePointsNumber > 0 && $rulePointsNumber > $roundedRulePointsNumber) {
                        $rulePointsNumber = $roundedRulePointsNumber + $spendPoints;
                    } else {
                        $rulePointsNumber = $roundedRulePointsNumber;
                    }
                    $stepsSecond = floor($rulePointsNumber / $spendPoints);
                }

                if ($rulePointsNumber < $rule->getSpendMinAmount($customer, $subtotal)) {
                    continue;
                }

                $stepsFirst = round($subtotal / $rule->getMonetaryStep($customer, $subtotal), 2, PHP_ROUND_HALF_DOWN);
                if ($stepsFirst != $subtotal / $rule->getMonetaryStep($customer, $subtotal)) {
                    ++$stepsFirst;
                }

                $steps = min($stepsFirst, $stepsSecond);

                $amount = $steps * $rule->getMonetaryStep($customer, $subtotal);
                $amount = min($amount, $subtotal);
                $totalBaseAmount += $amount;

                $pointsNumber = $pointsNumber - $rulePointsNumber;
                $totalPoints += $rulePointsNumber;

                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }
        $quoteSubTotal = $this->getQuoteSubtotal($quote);

        if ($totalBaseAmount > $quoteSubTotal) {//due to rounding we can have some error
            $totalBaseAmount = $quoteSubTotal;
        }
        $totalAmount = $totalBaseAmount;
        if ($quote->getBaseCurrencyCode() != $quote->getQuoteCurrencyCode()) {
            $totalAmount = $totalBaseAmount * $quote->getBaseToQuoteRate();
            $totalAmount = round($totalAmount, 2) * -1;
        }

        return new \Magento\Framework\DataObject([
            'points'      => $totalPoints,
            'base_amount' => $totalBaseAmount,
            'amount'      => $totalAmount,
        ]);
    }
}