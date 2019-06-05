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



namespace Mirasvit\Rewards\Block\Account\Tab\Link;

/**
 * Class Share
 *
 * Customer account tab "Share & Save".
 *
 * @package Mirasvit\Rewards\Block\Account\Tab\Link
 */
class Share extends \Magento\Framework\View\Element\Html\Link\Current
{
    public function __construct(
        \Mirasvit\Rewards\Helper\Account\Rule $accountRuleHelper,
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);

        $this->accountRuleHelper = $accountRuleHelper;
        $this->config = $config;
    }
    /**
     * Check if this tab should be shown
     *
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->isReferralActive() && !$this->socialRules()
        ) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return bool
     */
    public function isReferralActive()
    {
        return $this->config->getReferralIsActive();
    }

    /**
     * Check if we have at least one social rule
     *
     * @return bool
     */
    public function socialRules()
    {
        return $this->accountRuleHelper->getDisplaySocialRules()->count();
    }
}