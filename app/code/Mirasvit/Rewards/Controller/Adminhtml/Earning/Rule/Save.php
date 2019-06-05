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



namespace Mirasvit\Rewards\Controller\Adminhtml\Earning\Rule;

use Mirasvit\Rewards\Ui\Earning\Form\Modifier\AbstractModifier;
use Mirasvit\Rewards\Api\Data\Earning\RuleInterface;

class Save extends \Mirasvit\Rewards\Controller\Adminhtml\Earning\Rule
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $data = $this->prepareData($data);
            $earningRule = $this->_initEarningRule();

            $earningRule->addData($data);
            if (isset($data['rule'])) {
                $earningRule->loadPost($data['rule']);
            }

            try {
                $earningRule->save();
                $this->messageManager->addSuccessMessage(__('Earning Rule was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $type = $earningRule->getType();
                    if (!$type) {
                        $this->messageManager->addErrorMessage('Data not set');
                        $this->_redirect('*/*/add');
                        return;
                    }
                    switch ($type) {
                        case 'cart':
                            $path = '*/*/editCart';
                            break;

                        case 'product':
                            $path = '*/*/editProduct';
                            break;

                        case 'behavior':
                            $path = '*/*/editBehavior';
                            break;

                        default:
                            break;
                    }

                    $this->_redirect(
                        $path, ['id' => $earningRule->getId(), 'store' => $earningRule->getStoreId()]
                    );

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->backendSession->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find Earning Rule to save'));
        $this->_redirect('*/*/');
    }

    /**
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {
        $dataValues = ['monetary_step', 'qty_step', 'points_limit', 'param1', AbstractModifier::DATA_SCOPE_TIER];

        foreach ($dataValues as $value) {
            if (isset($data[$value])
                && !$data[$value]
            ) {
                unset($data[$value]);
            }
        }
        if (isset($data[AbstractModifier::DATA_SCOPE_TIER])) {
            $tiers = $data[AbstractModifier::DATA_SCOPE_TIER];
            $data[RuleInterface::KEY_TIERS_SERIALIZED] = $this->jsonHelper->serialize($tiers);
            unset($data[AbstractModifier::DATA_SCOPE_TIER]);
        }

        return $data;
    }
}
