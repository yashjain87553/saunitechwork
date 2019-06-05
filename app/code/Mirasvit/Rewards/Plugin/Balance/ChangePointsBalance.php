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


namespace Mirasvit\Rewards\Plugin\Balance;

use Mirasvit\Rewards\Helper\Balance;
use Mirasvit\Rewards\Api\Service\Customer\TierInterface;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class ChangePointsBalance
{
    public function __construct(
        TierInterface $tierService
    ) {
        $this->tierService = $tierService;
    }

    /**
     * @param Balance   $balance
     * @param \callable $proceed
     * @param array     $params
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundChangePointsBalance(Balance $balance, $proceed, ...$params)
    {
        $result = $proceed(...$params);
        if ($result) {
            $customerId = $params[0];
            if (is_object($customerId)) {
                $customerId = $customerId->getId();
            }
            $this->tierService->updateCustomerTier($customerId);
        }

        return $result;
    }
}