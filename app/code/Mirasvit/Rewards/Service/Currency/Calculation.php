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


namespace Mirasvit\Rewards\Service\Currency;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface as CurrencyHelper;

class Calculation extends AbstractHelper
{
    private $currencyHelper;
    private $dirCurrencyFactory;

    public function __construct(
        CurrencyFactory $dirCurrencyFactory,
        CurrencyHelper $currencyHelper,
        Context $context
    ) {
        parent::__construct($context);

        $this->currencyHelper  = $currencyHelper;
        $this->dirCurrencyFactory = $dirCurrencyFactory;
    }

    /**
     * @param float $amount
     * @param \Magento\Framework\Model\AbstractModel|string|null $fromCurrency
     * @param \Magento\Framework\Model\AbstractModel|string|null $toCurrency
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $store
     *
     * @return float
     */
    public function convertToCurrency($amount, $fromCurrency, $toCurrency, $store)
    {
        if (!$fromCurrency instanceof Currency) {
            $fromCurrency = $this->currencyHelper->getCurrency($store, $fromCurrency);
        }
        if (!$toCurrency instanceof Currency) {
            $toCurrency = $this->currencyHelper->getCurrency($store, $toCurrency);
        }

        try {
            $converted = $fromCurrency->convert($amount, $toCurrency);
        } catch (\Exception $e) {
            $converted = $this->calcCurrencyRate($amount, $fromCurrency, $toCurrency);
        }

        return $converted;
    }

    /**
     * @param float $amount
     * @param \Magento\Framework\Model\AbstractModel $fromCurrency
     * @param \Magento\Framework\Model\AbstractModel $toCurrency
     * @return float
     */
    public function calcCurrencyRate($amount, $fromCurrency, $toCurrency)
    {
        if ($fromCurrency->getCurrencyCode() == $toCurrency->getCurrencyCode()) {
            return $amount;
        }

        $currencyModel = $this->dirCurrencyFactory->create();
        $rates = $currencyModel->getCurrencyRates(
            $fromCurrency->getCurrencyCode(), $toCurrency->getCurrencyCode()
        );
        if (!count($rates) || !isset($rates[$toCurrency->getCurrencyCode()])) {
            $rates = $currencyModel->getCurrencyRates(
                $toCurrency->getCurrencyCode(), $fromCurrency->getCurrencyCode()
            );
            $currencyRate = 1 / $rates[$fromCurrency->getCurrencyCode()];
            $rates[$toCurrency->getCurrencyCode()] = $currencyRate;
        }
        $rate = $rates[$toCurrency->getCurrencyCode()];

        return $this->currencyHelper->round($amount * $rate);
    }
}
