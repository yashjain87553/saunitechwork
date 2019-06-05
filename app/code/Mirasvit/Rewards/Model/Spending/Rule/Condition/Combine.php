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



namespace Mirasvit\Rewards\Model\Spending\Rule\Condition;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Mirasvit\Rewards\Model\Spending\Rule\Condition\ProductFactory
     */
    protected $spendingRuleConditionProductFactory;

    /**
     * @var \Mirasvit\Rewards\Model\Spending\Rule\Condition\AddressFactory
     */
    protected $ruleConditionAddressFactory;

    /**
     * @var \Mirasvit\Rewards\Model\Spending\Rule\Condition\CustomFactory
     */
    protected $spendingRuleConditionCustomFactory;

    /**
     * @var \Mirasvit\Rewards\Helper\Rule
     */
    protected $rewardsRule;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     *
     * @param \Mirasvit\Rewards\Model\Spending\Rule\Condition\AddressFactory $ruleConditionAddressFactory
     * @param \Magento\Framework\App\Action\Context                          $context
     * @param \Magento\Rule\Model\Condition\Context                          $ruleContext
     * @param array                                                          $data
     */
    public function __construct(
        \Mirasvit\Rewards\Model\Spending\Rule\Condition\AddressFactory $ruleConditionAddressFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Rule\Model\Condition\Context $ruleContext,
        array $data = []
    ) {
        parent::__construct($ruleContext, $data);

        $this->ruleConditionAddressFactory = $ruleConditionAddressFactory;
        $this->ruleContext = $ruleContext;
        $this->context = $context;

        $this->setType('\\Mirasvit\\Rewards\\Model\\Spending\\Rule\\Condition\\Combine');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $type = \Mirasvit\Rewards\Model\Spending\Rule::TYPE_CART;
        if ($type == \Mirasvit\Rewards\Model\Spending\Rule::TYPE_CUSTOM) {
            $itemAttributes = $this->_getCustomAttributes();
            $condition = 'custom';
        } elseif ($type == \Mirasvit\Rewards\Model\Spending\Rule::TYPE_CART) {
            return $this->_getCartConditions();
        } else {
            $itemAttributes = $this->_getProductAttributes();
            $condition = 'product';
        }

        $attributes = [];
        foreach ($itemAttributes as $code => $label) {
            $group = $this->rewardsRule->getAttributeGroup($code);
            $attributes[$group][] = [
                'value' => '\\Mirasvit\\Rewards\\Model\\Spending\\Rule\\Condition\\'.$condition.'|'.$code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            [
                'value' => '\\Mirasvit\\Rewards\\Model\\Spending\\Rule\\Condition\\Combine',
                'label' => __('Conditions Combination'),
            ],
        ]);

        foreach ($attributes as $group => $arrAttributes) {
            $conditions = array_merge_recursive($conditions, [
                [
                    'label' => $group,
                    'value' => $arrAttributes,
                ],
            ]);
        }

        return $conditions;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function _getProductAttributes()
    {
        $productCondition = $this->spendingRuleConditionProductFactory->create();
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();

        return $productAttributes;
    }

    /**
     * @return array
     */
    protected function _getCartConditions()
    {
        $addressCondition = $this->ruleConditionAddressFactory->create();
        $addressAttributes = $addressCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($addressAttributes as $code => $label) {
            $attributes[] = ['value' => 'Mirasvit\Rewards\Model\Spending\Rule\Condition\Address|'.$code, 'label' => $label];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            [
                'value' => 'Magento\SalesRule\Model\Rule\Condition\Product\Found',
                'label' => __('Product attribute combination')
            ],
            [
                'value' => 'Magento\SalesRule\Model\Rule\Condition\Product\Subselect',
                'label' => __('Products subselection')
            ],
            ['value' => 'Magento\SalesRule\Model\Rule\Condition\Combine', 'label' => __('Conditions combination')],
            ['label' => __('Cart Attribute'), 'value' => $attributes],
        ]);

        $additional = new \Magento\Framework\DataObject();
        $this->context->getEventManager()->dispatch('salesrule_rule_condition_combine', ['additional' => $additional]);
        if ($additionalConditions = $additional->getConditions()) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }

    /**
     * @return array
     */
    protected function _getCustomAttributes()
    {
        $customCondition = $this->spendingRuleConditionCustomFactory->create();
        $customAttributes = $customCondition->loadAttributeOptions()->getAttributeOption();

        return $customAttributes;
    }
}
