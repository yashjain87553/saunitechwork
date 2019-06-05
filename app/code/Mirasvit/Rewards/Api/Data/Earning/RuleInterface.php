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


namespace Mirasvit\Rewards\Api\Data\Earning;

interface RuleInterface
{
    const KEY_FRONT_NAME = 'front_name';
    const KEY_BEHAVIOR_TRIGGER = 'behavior_trigger';
    const KEY_EARN_POINTS = 'earn_points';
    const KEY_MONETARY_STEP = 'monetary_step';
    const KEY_EMAIL_MESSAGE = 'email_message';
    const KEY_HISTORY_MESSAGE = 'history_message';
    const KEY_TIERS_SERIALIZED = 'tiers_serialized';

    const KEY_TIER_KEY_EARNING_STYLE = 'earning_style';
    const KEY_TIER_KEY_EARN_POINTS = 'earn_points';
    const KEY_TIER_KEY_MONETARY_STEP = 'monetary_step';
    const KEY_TIER_KEY_POINTS_LIMIT = 'points_limit';
    const KEY_TIER_KEY_QTY_STEP = 'qty_step';
    const KEY_TIER_KEY_TRANSFER_TO_GROUP = 'transfer_to_group';
}