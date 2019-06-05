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



namespace Mirasvit\Rewards\Controller\Account;

use Magento\Framework\Exception\NotFoundException;

class Share extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->isReferralActive() && !$this->socialRules()
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        return parent::dispatch($request);
    }
    /**
     * @return bool
     */
    public function isReferralActive()
    {
        return $this->config->getReferralIsActive();
    }

    /**
     * @return bool
     */
    public function socialRules()
    {
        return $this->accountRuleHelper->getDisplaySocialRules()->count();
    }
}
