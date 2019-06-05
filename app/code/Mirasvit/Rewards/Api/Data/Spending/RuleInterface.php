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


namespace Mirasvit\Rewards\Api\Data\Spending;

interface RuleInterface
{
    const KEY_FRONT_NAME = 'front_name';
    const KEY_SPEND_POINTS = 'spend_points';
    const KEY_MONETARY_STEP = 'monetary_step';
    const KEY_TIERS_SERIALIZED = 'tiers_serialized';

    const KEY_TIER_KEY_SPENDING_STYLE = 'spending_style';
    const KEY_TIER_KEY_SPEND_POINTS = 'spend_points';
    const KEY_TIER_KEY_MONETARY_STEP = 'monetary_step';
    const KEY_TIER_KEY_SPEND_MIN_POINTS = 'spend_min_points';
    const KEY_TIER_KEY_SPEND_MAX_POINTS = 'spend_max_points';
}