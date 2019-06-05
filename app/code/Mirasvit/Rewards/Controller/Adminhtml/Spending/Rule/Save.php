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



namespace Mirasvit\Rewards\Controller\Adminhtml\Spending\Rule;

use Mirasvit\Rewards\Api\Data\Spending\RuleInterface;
use Mirasvit\Rewards\Ui\Spending\Form\Modifier\AbstractModifier;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Mirasvit\Rewards\Controller\Adminhtml\Spending\Rule
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($data = $this->getRequest()->getParams()) {
            $spendingRule = $this->_initSpendingRule();

            try {
                $spendingRule->addData($this->prepareData($data));
                if (isset($data['rule'])) {
                    $spendingRule->loadPost($data['rule']);
                }

                if (!$spendingRule->validateTierFields()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Field 'Spend maximum' can not be less or equal then field 'Spend minimum'")
                    );
                }
                $spendingRule->getResource()->save($spendingRule);
                $this->messageManager->addSuccessMessage(__('Spending Rule was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit',
                        ['id' => $spendingRule->getId(), 'store' => $spendingRule->getStoreId()]
                    );

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->backendSession->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('spending_rule_id')]);

                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find Spending Rule to save'));
        $this->_redirect('*/*/');
    }

    /**
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {
        if (isset($data[AbstractModifier::DATA_SCOPE_TIER])) {
            $tiers = $data[AbstractModifier::DATA_SCOPE_TIER];
            foreach ($tiers as $tier) {
                $monetaryStep = $tier[RuleInterface::KEY_TIER_KEY_MONETARY_STEP];
                if (!empty($monetaryStep) && preg_match('/[^\d.%]/', $monetaryStep)
                ) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Field 'Customer receive Y discount' should be number or percent")
                    );
                }
                if (strpos($monetaryStep, '%') !== false) {
                    $monetaryStep = str_replace('%', '', $monetaryStep);
                    if ($monetaryStep > 100) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("Percent should be equal or less then 100")
                        );
                    }
                }
            }
            $data[RuleInterface::KEY_TIERS_SERIALIZED] = $this->jsonHelper->serialize($tiers);
            unset($data[AbstractModifier::DATA_SCOPE_TIER]);
        }

        return $data;
    }
}
