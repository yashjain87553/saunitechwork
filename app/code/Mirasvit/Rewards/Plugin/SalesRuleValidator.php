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


namespace Mirasvit\Rewards\Plugin;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Validator;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class SalesRuleValidator
{
    /**
     * @var int
     */
    private $itemIndex = 0;

    /**
     * @var array
     */
    private $quoteProductIds = [];

    /**
     * @var array
     */
    private $pointsInfo = [];

    /**
     * @var array
     */
    private $quoteItemsPrice = [];

    /**
     * @var AbstractItem
     */
    private $item;

    /**
     * @var Address
     */
    private $itemAddress;

    public function __construct(
        \Mirasvit\Rewards\Helper\Balance\Spend $spendHelper,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Service\Quote\Item\CalcPriceService $calcPriceService,
        \Mirasvit\Rewards\Service\RoundService $roundService,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Tax\Model\TaxCalculation $taxCalculation,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $taxData
    ) {
        $this->spendHelper      = $spendHelper;
        $this->rewardsPurchase  = $rewardsPurchase;
        $this->rewardsData      = $rewardsData;
        $this->config           = $config;
        $this->calcPriceService = $calcPriceService;
        $this->roundService     = $roundService;
        $this->moduleManager    = $moduleManager;
        $this->taxCalculation   = $taxCalculation;
        $this->taxConfig        = $taxConfig;
        $this->taxData          = $taxData;
    }

    /**
     * @param Validator     $validator
     * @param \callable     $proceed
     * @param AbstractItem  $item
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundProcess(Validator $validator, $proceed, AbstractItem $item)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $returnValue = $proceed($item);
        return $returnValue;
        if ($returnValue && $this->config->getCalculateTotalFlag()) {
            $this->item = $item;
            $quote = $this->item->getQuote();
            $purchase = $this->rewardsPurchase->getByQuote($quote);
            if (!$purchase || empty($purchase->getQuoteProductIds())) {
                return $returnValue;
            }
            $this->quoteProductIds = $purchase->getQuoteProductIds();
            $items = $this->getAddressItems($item);
            if (empty($this->quoteItemsPrice[$this->item->getId()])) {
                $this->quoteItemsPrice = $this->calcPriceService->getQuotePrices($items);
            }
            $this->process();
        }
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);

        return $returnValue;
    }

    /**
     * @param Validator $validator
     * @param \callable $proceed
     * @param Address   $address
     * @param string    $separator
     * @return Validator
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPrepareDescription(Validator $validator, $proceed, $address, $separator = ', ')
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
//        $descriptions = (array)$address->getDiscountDescriptionArray();
//
//        $quote = $address->getQuote();
//        $purchase = $this->rewardsPurchase->getByQuote($quote);
//        if ($purchase && $purchase->getSpendAmount() > 0) {
//            $descriptions[] = $this->rewardsData->getPointsName();
//        }
//        $address->setDiscountDescriptionArray($descriptions);
        $returnValue = $proceed($address, $separator);

        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
        return $returnValue;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function process()
    {
        $this->itemIndex++;
        if (!$this->canProcess() || !in_array($this->item->getId(), $this->quoteProductIds)) {
            return $this;
        }

        $rewardsPrices = $this->quoteItemsPrice[$this->item->getId()];
        $this->item->setRewardsTotalPrice($rewardsPrices['price']);
        $this->item->setRewardsBaseTotalPrice($rewardsPrices['basePrice']);

        if ($this->item->getRewardsTotalPrice() == 0) {
            return $this;
        }

        $this->calcPoints();

        if ($this->pointsInfo['total'] == 0) {//protection from division on zero
            $this->pointsInfo['total'] = $this->item->getRewardsTotalPrice();
        }
        if ($this->pointsInfo['baseTotal'] == 0) {//protection from division on zero
            $this->pointsInfo['baseTotal'] = $this->item->getRewardsBaseTotalPrice();
        }

        $discount = $this->item->getRewardsTotalPrice() / $this->pointsInfo['total'] * $this->pointsInfo['spendAmount'];
        $baseDiscount = $this->item->getRewardsBaseTotalPrice() / $this->pointsInfo['baseTotal'] *
            $this->pointsInfo['baseSpendAmount'];

        $rate = $this->getPercentTax();
        $discountDiff = 0;
        $baseDiscountDiff = 0;
        if ($rate) {
            $delta = $this->item->getRewardsTotalPrice() - $discount;
            $baseDelta = $this->item->getRewardsBaseTotalPrice() - $baseDiscount;
            $rewardsDiscount = $this->item->getData(OrderItemInterface::ROW_TOTAL) - ($delta / (1 + $rate));
            $rewardsBaseDiscount = $this->item->getData(OrderItemInterface::BASE_ROW_TOTAL) - ($baseDelta / (1+$rate));
            $this->item->getQuote()->setItemsRewardsTaxDiscount(
                $this->item->getQuote()->getItemsRewardsTaxDiscount() + $discount - $rewardsDiscount
            );
            $this->item->getQuote()->setBaseItemsRewardsTaxDiscount(
                $this->item->getQuote()->getBaseItemsRewardsTaxDiscount() + $baseDiscount - $rewardsBaseDiscount
            );
            $discount = $rewardsDiscount;
            $baseDiscount = $rewardsBaseDiscount;
        }
        if ($discount > $this->item->getRewardsTotalPrice()) {
            $discount = $this->item->getRewardsTotalPrice();
        }
        if ($baseDiscount > $this->item->getRewardsBaseTotalPrice()) {
            $baseDiscount = $this->item->getRewardsBaseTotalPrice();
        }

        $itemsRewardsDiscount = $this->item->getQuote()->getItemsRewardsDiscount();
        $baseItemsRewardsDiscount = $this->item->getQuote()->getBaseItemsRewardsDiscount();
        $this->item->getQuote()->setItemsRewardsDiscount($itemsRewardsDiscount + $discount + $discountDiff);
        $this->item->getQuote()->setBaseItemsRewardsDiscount($baseItemsRewardsDiscount + $baseDiscount + $baseDiscountDiff);

        $discount += $this->item->getDiscountAmount();
        $baseDiscount += $this->item->getBaseDiscountAmount();

        $discount = $this->roundPriceWithFaonniPrice($discount);

        if (abs($this->item->getQuote()->getBaseAwStoreCreditAmount()) > 0) {
            $discount = round($discount, 2);
            $baseDiscount = round($baseDiscount, 2);
        }
        $this->item->setDiscountAmount($discount);
        $this->item->setBaseDiscountAmount($baseDiscount);

        if ($this->item->getQuote()->getItemsCount() == $this->itemIndex) {
            $this->quoteItemsPrice = [];
        }

        return $this;
    }

    /**
     * @param float $price
     * @return float
     */
    private function roundPriceWithFaonniPrice($price)
    {
        if (!$this->moduleManager->isEnabled('Faonni_Price')) {
            return $price;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('Faonni\Price\Helper\Data');
        $math = $objectManager->create('Faonni\Price\Model\Math');
        if (!$helper->isEnabled() ||
            !$helper->isRoundingDiscount()
        ) {
            return $price;
        }

        return $math->round($price);
    }

    /**
     * @return float
     */
    protected function fixShippingTaxRounding()
    {
        $delta = 0;
        $taxes = $this->item->getAddress()->getItemsAppliedTaxes();
        if (empty($taxes['shipping'])) {
            return $delta;
        }
        $tax = array_shift($taxes['shipping']);
        $shippingTax = $this->item->getAddress()->getShippingAmount() * $tax['percent'] / 100;

        return abs($this->item->getAddress()->getShippingTaxAmount() - round($shippingTax, 2));
    }

    /**
     * @return float
     */
    protected function fixBaseShippingTaxRounding()
    {
        $delta = 0;
        $taxes = $this->item->getAddress()->getItemsAppliedTaxes();
        if (empty($taxes['shipping'])) {
            return $delta;
        }
        $tax = array_shift($taxes['shipping']);
        $shippingTax = $this->item->getAddress()->getBaseShippingAmount() * $tax['percent'] / 100;

        return abs($this->item->getAddress()->getBaseShippingTaxAmount() - round($shippingTax, 2));
    }

    /**
     * @return bool
     */
    protected function applyToShipping()
    {
        return $this->config->getGeneralIsSpendShipping() &&
            $this->pointsInfo['totalSpendAmount'] > $this->pointsInfo['baseTotal'];
    }

    /**
     * @return void
     */
    protected function calcPoints()
    {
        $quoteItems = $this->getAddressItems();
        $rewardsTotal = $rewardsBaseTotal = 0;
        foreach ($quoteItems as $quoteItem) {
            if (in_array($quoteItem->getId(), $this->quoteProductIds) &&
                isset($this->quoteItemsPrice[$quoteItem->getId()])
            ) {
                $rewardsPrices = $this->quoteItemsPrice[$quoteItem->getId()];
                $rewardsTotal += $rewardsPrices['price'];
                $rewardsBaseTotal += $rewardsPrices['basePrice'];
            }
        }
        $total = $rewardsTotal;
        $baseTotal = $rewardsBaseTotal;

        $purchase = $this->rewardsPurchase->getByQuote($this->item->getQuote());
        $baseSpendAmount = $purchase->getBaseSpendAmount();

        if (!$baseTotal) {
            $baseTotal = $total;
        }
        if ($baseSpendAmount > $baseTotal) {
            $baseSpendAmount = $baseTotal;
        }

        $currencyRate = 1;
        if ($baseTotal > 0 && !$this->isCustomRoundingEnabled()) { //for some reason subtotal can be 0
            $currencyRate = $total / $baseTotal;
        }
        $spendAmount = round($baseSpendAmount * $currencyRate, 2, PHP_ROUND_HALF_DOWN);

        $this->pointsInfo = [
            'tax'              => $this->itemAddress->getTaxAmount(),
            'total'            => $total,
            'baseTotal'        => $baseTotal,
            'spendAmount'      => $spendAmount,
            'baseSpendAmount'  => $baseSpendAmount,
            'totalSpendAmount' => $purchase->getSpendAmount(),
            'currencyRate'     => $currencyRate,
        ];
    }


    /**
     * We need this because Faonni_Price changes total without basetotal
     *
     * @return bool
     */
    protected function isCustomRoundingEnabled()
    {
        $address = $this->itemAddress;
        return $this->moduleManager->isEnabled('Faonni_Price') &&
            $address->getQuote()->getBaseCurrencyCode() == $address->getQuote()->getQuoteCurrencyCode();
    }

    /**
     * @return bool|float
     */
    private function getPercentTax()
    {
        $rate = $this->taxCalculation->getCalculatedRate(
            $this->item->getData('tax_class_id'),
            $this->item->getQuote()->getCustomerId(),
            $this->item->getQuote()->getStoreId()
        );

        return $this->taxConfig->applyTaxAfterDiscount() && !$this->taxConfig->priceIncludesTax()
            && $this->config->getGeneralIsIncludeTaxSpending() ? ($rate / 100) : 0;
    }

    /**
     * @return bool
     */
    private function canProcess()
    {
        $quote = $this->item->getQuote();

        if ($this->rewardsData->isMultiship($this->itemAddress)) {
            return false;
        }
        if (!$quote->getId()) {
            return false;
        }

        $purchase = $this->rewardsPurchase->getByQuote($quote);

        if (!$purchase->getSpendAmount()) {
            return false;
        }
        $spendAmount = $purchase->getSpendAmount();
        if ($spendAmount == 0) {
            return false;
        }
        if (empty($this->quoteItemsPrice[$this->item->getId()])) {
            return false;
        }

        return true;
    }

    /**
     * @return void
     */
    private function initItemAddress()
    {
        if ($this->itemAddress) {
            return;
        }
        $item = $this->item;
        $this->itemAddress = $item->getAddress();
        if (!count($this->itemAddress->getAllItems()) &&
            $item->getAddress()->getAddressType() == Address::ADDRESS_TYPE_SHIPPING
        ) {
            $this->itemAddress = $item->getQuote()->getBillingAddress();
        }
    }

    /**
     * @return AbstractItem[]
     */
    private function getAddressItems()
    {
        $this->initItemAddress();
        $items = $this->itemAddress->getAllItems();

        return $items;
    }
}