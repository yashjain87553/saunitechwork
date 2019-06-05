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


namespace Mirasvit\Rewards\Plugin\Affiliate;

use Mirasvit\Affiliate\Api\Data\AccountInterface;
use Mirasvit\Affiliate\Service\AccountService;
use Mirasvit\Rewards\Model\Config;

class JoinPlugin
{
    /**
     * @param \Mirasvit\Rewards\Helper\Behavior $rewardsBehavior
     */
    public function __construct(
        \Mirasvit\Rewards\Helper\Behavior $rewardsBehavior
    ) {
        $this->rewardsBehavior = $rewardsBehavior;
    }

    /**
     * @param AccountService $accountService
     * @param AccountInterface $account
     * @return AccountInterface
     */
    public function afterCreateAccount(AccountService $accountService, AccountInterface $account)
    {
        $this->rewardsBehavior->processRule(Config::BEHAVIOR_TRIGGER_AFFILIATE_CREATE, $account->getCustomerId());

        return $account;
    }
}