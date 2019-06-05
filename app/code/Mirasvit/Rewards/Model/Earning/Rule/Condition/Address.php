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


namespace Mirasvit\Rewards\Model\Earning\Rule\Condition;

class Address extends \Magento\SalesRule\Model\Rule\Condition\Address
{
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods,
        \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods,
        array $data = []
    ) {
        parent::__construct(
            $context, $directoryCountry, $directoryAllregion, $shippingAllmethods, $paymentAllmethods, $data
        );

        $this->taxConfig = $taxConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();

        $attributes = $this->getAttributeOption();
        $attributes['payment_method'] = __('Payment Method');

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'payment_method':
                return 'select';
        }

        return parent::getInputType();
    }

    /**
     * {@inheritdoc}
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'payment_method':
                return 'select';
        }
        return parent::getValueElementType();
    }

    /**
     * {@inheritdoc}
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'payment_method':
                    $options = $this->_paymentAllmethods->toOptionArray();
                    break;
                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return parent::getValueSelectOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $model;

        if (
            'base_subtotal' == $this->getAttribute() &&
            $this->taxConfig->displayCartSubtotalInclTax($address->getQuote()->getStore())
        ) {
            $this->setAttribute('subtotal_incl_tax');
        }

        if ('payment_method' == $this->getAttribute() && !$address->hasPaymentMethod()) {
            $address->setPaymentMethod($model->getQuote()->getPayment()->getMethod());
        }

        return parent::validate($address);
    }
}
