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


namespace Mirasvit\Rewards\Model\Total\Quote;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Mirasvit\Rewards\Model\Purchase;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
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

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var Purchase
     */
    private $purchase;

    /**
     * @var int
     */
    private $used = 0;

    /**
     * @var int
     */
    private $baseUsed = 0;

    private $spendHelper;
    private $rewardsPurchase;
    private $rewardsData;
    private $config;
    private $calcPriceService;
    private $roundService;
    private $shippingService;
    private $moduleManager;
    private $taxCalculation;
    private $taxConfig;
    private $taxData;

    public function __construct(
        \Mirasvit\Rewards\Helper\Balance\Spend $spendHelper,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Service\Quote\Item\CalcPriceService $calcPriceService,
        \Mirasvit\Rewards\Service\RoundService $roundService,
        \Mirasvit\Rewards\Service\ShippingService $shippingService,
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
        $this->shippingService  = $shippingService;
        $this->moduleManager    = $moduleManager;
        $this->taxCalculation   = $taxCalculation;
        $this->taxConfig        = $taxConfig;
        $this->taxData          = $taxData;

        $this->setCode('rewards_discount');
    }

    /**
     * {@inheritdoc}
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $this->quote = $quote;

        $this->itemAddress = $address = $shippingAssignment->getShipping()->getAddress();

        $this->resetCalcInfo();

        $address->setDiscountDescription('');

        $purchase = $this->getPurchase();
        if ($this->config->getDisableRewardsCalculation() || !$purchase) {
            return $this;
        }

        if (!$purchase->getSpendAmount() || empty($purchase->getQuoteProductIds())) {
            return $this;
        }

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        $address->setDiscountDescription(__('Rewards Discount'));

        $this->quoteItemsPrice = $this->calcPriceService->getQuotePrices($items);
        $this->quoteProductIds = $purchase->getQuoteProductIds();

        $this->calcPoints($items);

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            $this->item = $item;
            if (!$this->canProcess()) {
                continue;
            }
            $this->process();
        }
        $this->used = $this->quote->getItemsRewardsDiscount();
        $this->baseUsed = $this->quote->getBaseItemsRewardsDiscount();

        $this->fixDiscount();

        if ($purchase->getBaseSpendAmount() <= $this->baseUsed) {
            $this->updateTotals($total);

            $purchase->setBaseSpendAmount($this->baseUsed);
            $purchase->setSpendAmount($this->used);
            $purchase->save();

            return $this;
        }

        /** Shipping discount */
        $this->processShipping();

        $this->fixDiscount();

        // we really need this
        $this->baseUsed = round($this->baseUsed, 2);
        $this->used = round($this->used, 2);

        $purchase->setBaseSpendAmount($this->baseUsed);
        $purchase->setSpendAmount($this->used);
        $purchase->save();

        $this->updateTotals($total);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return void
     */
    private function updateTotals($total)
    {
        $total->setBaseTotalAmount($this->getCode(), -$this->baseUsed);
        $total->setTotalAmount($this->getCode(), -$this->used);
        $total->setBaseRewardsDiscount($this->baseUsed);
        $total->setRewardsDiscount($this->used);

        if ($total->getBaseGrandTotal()) {
            $total->setBaseGrandTotal($total->getBaseGrandTotal() - $this->baseUsed);
        }
        if ($total->getGrandTotal()) {
            $total->setGrandTotal($total->getGrandTotal() - $this->used);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        if ($quote->getIsVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }

        $amount = $total->getBaseRewardsDiscount();
        if (!$amount) {
            $purchase = $this->rewardsPurchase->getByQuote($quote);
            if ($purchase) {
                $amount = $purchase->getSpendAmount();
            }
        }

        if ($amount != 0) {
            $result = [
                'code'  => $this->getCode(),
                'title' => __('Rewards Discount'),
                'value' => -$amount,
                'area'  => 'footer',
            ];

            $address->addTotal($result);
        }

        return $result;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function processShipping()
    {
        if (!$this->canProcessShipping()) {
            return $this;
        }

        $purchase = $this->getPurchase();

        $currencyRate = 1;
        if (!$this->isCustomRoundingEnabled()) {
            $currencyRate = $this->quote->getBaseToQuoteRate();
        }

        $baseShippingSpendAmount = $purchase->getBaseSpendAmount() - $this->quote->getBaseItemsRewardsDiscount() -
            $this->quote->getBaseItemsRewardsTaxDiscount();
        if ($baseShippingSpendAmount < 0.0001) {
            $baseShippingSpendAmount = 0;
        }
        if ($baseShippingSpendAmount <= 0) {
            return $this;
        }

        $baseShippingDiscount = $baseShippingSpendAmount;
        $baseShippingDiscount += $this->fixBaseShippingTaxRounding();
        $shippingDiscount = $baseShippingDiscount * $currencyRate;
        $shippingDiscount += $this->fixShippingTaxRounding();
        $shippingDiscount = round($shippingDiscount, 2, PHP_ROUND_HALF_DOWN);

        $shippingDiscount += $this->itemAddress->getShippingDiscountAmount();
        $baseShippingDiscount += $this->itemAddress->getBaseShippingDiscountAmount();

        $this->itemAddress->setShippingDiscountAmount($shippingDiscount);
        $this->itemAddress->setBaseShippingDiscountAmount($baseShippingDiscount);

        $this->baseUsed += $baseShippingDiscount;
        $this->used += $shippingDiscount;

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function process()
    {
        $rewardsPrices = $this->quoteItemsPrice[$this->item->getId()];
        $this->item->setRewardsTotalPrice($rewardsPrices['price']);
        $this->item->setRewardsBaseTotalPrice($rewardsPrices['basePrice']);

        if ($this->item->getRewardsTotalPrice() == 0) {
            return $this;
        }

        if ($this->pointsInfo['total'] == 0) {//protection from division on zero
            $this->pointsInfo['total'] = $this->item->getRewardsTotalPrice();
        }
        if ($this->pointsInfo['baseTotal'] == 0) {//protection from division on zero
            $this->pointsInfo['baseTotal'] = $this->item->getRewardsBaseTotalPrice();
        }

        $discount = $this->item->getRewardsTotalPrice() / $this->pointsInfo['total'] * $this->pointsInfo['spendAmount'];
        $baseDiscount = $this->item->getRewardsBaseTotalPrice() / $this->pointsInfo['baseTotal'] *
            $this->pointsInfo['baseSpendAmount'];

        if ($discount > $this->item->getRewardsTotalPrice()) {
            $discount = $this->item->getRewardsTotalPrice();
        }
        if ($baseDiscount > $this->item->getRewardsBaseTotalPrice()) {
            $baseDiscount = $this->item->getRewardsBaseTotalPrice();
        }

        $itemsRewardsDiscount = $this->quote->getItemsRewardsDiscount();
        $baseItemsRewardsDiscount = $this->quote->getBaseItemsRewardsDiscount();
        $this->quote->setItemsRewardsDiscount($itemsRewardsDiscount + $discount);
        $this->quote->setBaseItemsRewardsDiscount($baseItemsRewardsDiscount + $baseDiscount);

        $discount = $this->roundPriceWithFaonniPrice($discount);
        if (abs($this->quote->getBaseAwStoreCreditAmount()) > 0) {
            $discount = round($discount, 2);
            $baseDiscount = round($baseDiscount, 2);
        }

        $this->item->setRewardsDiscountAmount($discount);
        $this->item->setBaseRewardsDiscountAmount($baseDiscount);

        return $this;
    }

    /**
     * @return bool
     */
    private function canProcess()
    {
        if ($this->rewardsData->isMultiship($this->itemAddress)) {
            return false;
        }
        if ($this->item->getParentItem()) {
            return false;
        }
        if (!in_array($this->item->getId(), $this->quoteProductIds)) {
            return false;
        }
        if (empty($this->quoteItemsPrice[$this->item->getId()])) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function canProcessShipping()
    {
        if (!$this->applyToShipping()) {
            return false;
        }

        return true;
    }

    /**
     * @param array $items
     * @return void
     */
    private function calcPoints($items)
    {
        $rewardsTotal = $rewardsBaseTotal = 0;
        foreach ($items as $quoteItem) {
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

        $purchase = $this->getPurchase($this->quote);
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
            'currencyRate'     => $currencyRate,
        ];
    }

    /**
     * We need this because Faonni_Price changes total without basetotal
     *
     * @return bool
     */
    private function isCustomRoundingEnabled()
    {
        $address = $this->itemAddress;
        return $this->moduleManager->isEnabled('Faonni_Price') &&
            $address->getQuote()->getBaseCurrencyCode() == $address->getQuote()->getQuoteCurrencyCode();
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
     * @param Quote $quote
     *
     * @return bool|Purchase
     */
    private function getPurchase()
    {
        if (empty($this->purchase)) {
            $this->purchase = $this->rewardsPurchase->getByQuote($this->quote);
        }

        return $this->purchase;
    }

    /**
     * @return bool
     */
    private function applyToShipping()
    {
        return $this->config->getGeneralIsSpendShipping();
    }

    /**
     * @return float
     */
    private function fixShippingTaxRounding()
    {
        $delta = 0;
        $taxes = $this->itemAddress->getItemsAppliedTaxes();
        if (empty($taxes['shipping'])) {
            return $delta;
        }
        $tax = array_shift($taxes['shipping']);
        $shippingTax = $this->getShippingAmount() * $tax['percent'] / 100;

        return abs($this->itemAddress->getShippingTaxAmount() - round($shippingTax, 2));
    }

    /**
     * @return float
     */
    private function fixBaseShippingTaxRounding()
    {
        $delta = 0;
        $taxes = $this->itemAddress->getItemsAppliedTaxes();
        if (empty($taxes['shipping'])) {
            return $delta;
        }
        $tax = array_shift($taxes['shipping']);
        $shippingTax = $this->getBaseShippingAmount() * $tax['percent'] / 100;

        return abs($this->itemAddress->getBaseShippingTaxAmount() - round($shippingTax, 2));
    }

    /**
     * @return float
     */
    private function getShippingAmount()
    {
        return $this->shippingService->getRewardsShippingPrice($this->itemAddress);
    }

    /**
     * @return float
     */
    private function getBaseShippingAmount()
    {
        return $this->shippingService->getBaseRewardsShippingPrice($this->itemAddress);
    }

    /**
     * @return void
     */
    private function resetCalcInfo()
    {
        $this->used = 0;
        $this->baseUsed = 0;
        $this->quote->setItemsRewardsDiscount(0);
        $this->quote->setBaseItemsRewardsDiscount(0);
        $this->quote->setItemsRewardsTaxDiscount(0);
        $this->quote->setBaseItemsRewardsTaxDiscount(0);

        $this->purchase = null;
    }

    /**
     * @return void
     */
    private function fixDiscount()
    {
        $purchase = $this->getPurchase($this->quote);
        if ($this->baseUsed > $purchase->getBaseSpendAmount()) {
            $currencyRate = $this->quote->getBaseToQuoteRate();
            $this->baseUsed = $purchase->getBaseSpendAmount();
            $this->used = $this->baseUsed * $currencyRate;
        }
    }
}
