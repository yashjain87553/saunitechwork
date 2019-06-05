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



namespace Mirasvit\Rewards\Controller\Adminhtml\Spending;

abstract class Rule extends \Magento\Backend\App\Action
{
    protected $jsonHelper;
    protected $spendingRuleFactory;
    protected $localeDate;
    protected $registry;
    protected $context;
    protected $backendSession;
    protected $resultFactory;

    public function __construct(
        \Mirasvit\Rewards\Helper\Json $jsonHelper,
        \Mirasvit\Rewards\Model\Spending\RuleFactory $spendingRuleFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->spendingRuleFactory = $spendingRuleFactory;
        $this->localeDate = $localeDate;
        $this->registry = $registry;
        $this->context = $context;
        $this->backendSession = $context->getSession();
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Rewards::rewards');
        $resultPage->getConfig()->getTitle()->prepend(__('Reward Points'));
        $resultPage->getConfig()->getTitle()->prepend(__('Spending Rules'));

        return $resultPage;
    }

    /**
     * @return \Mirasvit\Rewards\Model\Spending\Rule
     */
    public function _initSpendingRule()
    {
        $spendingRule = $this->spendingRuleFactory->create();
        if ($this->getRequest()->getParam('id')) {
            $spendingRule->load($this->getRequest()->getParam('id'));
            if ($storeId = (int) $this->getRequest()->getParam('store')) {
                $spendingRule->setStoreId($storeId);
            }
        }

        $this->registry->register('current_spending_rule', $spendingRule);

        return $spendingRule;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Rewards::reward_points_spending_rule');
    }

    /************************/
}
