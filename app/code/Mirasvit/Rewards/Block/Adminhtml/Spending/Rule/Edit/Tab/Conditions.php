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



namespace Mirasvit\Rewards\Block\Adminhtml\Spending\Rule\Edit\Tab;

class Conditions extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $widgetFormRendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var string
     */
    protected $_nameInLayout = 'conditions_serialized';

    /**
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $widgetFormRendererFieldset
     * @param \Magento\Rule\Block\Conditions                       $conditions
     * @param \Magento\Framework\Data\FormFactory                  $formFactory
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\Backend\Block\Widget\Context                $context
     * @param array                                                $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $widgetFormRendererFieldset,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->widgetFormRendererFieldset = $widgetFormRendererFieldset;
        $this->conditions = $conditions;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->context = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $spendingRule = $this->registry->registry('current_spending_rule');
        $form = $this->formFactory->create();

        $formName = 'rewards_spending_rule_form';
        $fieldsetName = 'rule_actions_fieldset';

        $form->setHtmlIdPrefix($formName);


        $url = $this->getUrl(
            'sales_rule/promo_quote/newConditionHtml/form/'.$formName.$fieldsetName,
            ['form_namespace' => $formName]
        );
        $renderer = $this->widgetFormRendererFieldset
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($url)
            ->setFieldSetId($formName.$fieldsetName);

        $fieldset = $form->addFieldset($fieldsetName, [
            'legend' => __('Apply the rule only if the following conditions are met (leave blank for all products)'),
        ])->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', [
            'name' => 'conditions',
            'label' => __('Conditions'),
            'title' => __('Conditions'),
            'data-form-part' => $formName,
        ])->setRule($spendingRule)->setRenderer($this->conditions);

        $form->setValues($spendingRule->getData());
        $this->setConditionFormName($spendingRule->getConditions(), $formName);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Handles addition of form name to condition and its conditions.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string $formName
     * @return void
     */
    private function setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $conditions, $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }

    /************************/
}
