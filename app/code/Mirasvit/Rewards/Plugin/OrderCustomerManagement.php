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


namespace Mirasvit\Rewards\Plugin;

use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\Store;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class OrderCustomerManagement
{
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        \Mirasvit\Rewards\Helper\Balance\Order $balanceOrderHelper
    ) {
        $this->orderRepository    = $orderRepository;
        $this->balanceOrderHelper = $balanceOrderHelper;
    }

    /**
     * @param OrderCustomerManagementInterface $config
     * @param \callable $proceed
     * @param int       $orderId
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCreate(OrderCustomerManagementInterface $config, $proceed, $orderId)
    {
        $result = $proceed($orderId);
        if ($result->getId()) {
            $order = $this->orderRepository->get($orderId);
            if ($order->getId()) {
                $this->balanceOrderHelper->earnBehaviorOrderPoints($order);
            }
        }

        return $result;
    }
}