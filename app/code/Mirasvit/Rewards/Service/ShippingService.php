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



namespace Mirasvit\Rewards\Service;

use Magento\Quote\Model\Quote\Address;
use Magento\Tax\Model\Config as TaxConfig;
use Mirasvit\Rewards\Model\Config;

class ShippingService
{
    public function __construct(
        TaxConfig $taxConfig,
        Config $config
    ) {
        $this->taxConfig = $taxConfig;
        $this->config = $config;
    }

    /**
     * @param Address $shippingAddress
     * @return float
     */
    public function getBaseRewardsShippingPrice($shippingAddress)
    {
        if ($this->config->getGeneralIsIncludeTaxSpending()) {
            return $shippingAddress->getBaseShippingInclTax();
        } else {
            if ($this->taxConfig->shippingPriceIncludesTax()) {
                return $shippingAddress->getBaseShippingInclTax() - $shippingAddress->getBaseShippingTaxAmount();
            } else {
                return $shippingAddress->getBaseShippingAmount();
            }
        }
    }

    /**
     * @param Address $shippingAddress
     * @return float
     */
    public function getRewardsShippingPrice($shippingAddress)
    {
        if ($this->config->getGeneralIsIncludeTaxSpending()) {
            return $shippingAddress->getShippingInclTax();
        } else {
            if ($this->taxConfig->shippingPriceIncludesTax()) {
                return $shippingAddress->getShippingInclTax() - $shippingAddress->getShippingTaxAmount();
            } else {
                return $shippingAddress->getShippingAmount();
            }
        }
    }
}