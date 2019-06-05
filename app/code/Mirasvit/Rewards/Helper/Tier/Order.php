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



namespace Mirasvit\Rewards\Helper\Tier;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Mirasvit\Rewards\Model\Config $rewardsConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->rewardsConfig = $rewardsConfig;
        $this->resource = $resource;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return int
     */
    public function getSumForLastDays($customer, $days)
    {
        $customerId = $customer->getId();
        $website    = $customer->getStore()->getWebsite();

        $fields = ['base_subtotal_invoiced'];
        if ($this->rewardsConfig->getTierCalcForOrderInclDiscount($website)) {
            $fields[] = 'base_discount_invoiced';
        }
        if ($this->rewardsConfig->getTierCalcForOrderInclShipping($website)) {
            $fields[] = 'base_shipping_invoiced';
        }
        if ($this->rewardsConfig->getTierCalcForOrderInclTax($website)) {
            $fields[] = 'base_tax_invoiced';
        }

        $resource = $this->resource;
        $table = $resource->getTableName('sales_order');

        $selectedStatuses = explode(',', $this->rewardsConfig->getTierCalcForOrderInStatuses($website));

        $statuses = [];
        foreach ($selectedStatuses as $status) {
            $statuses[] = "'" . $status . "'";
        }

        $sql = "SELECT SUM(" . implode('+', $fields) . ") " .
            "FROM $table " .
            "WHERE customer_id = ? AND status IN (" . implode(',', $statuses) . ")";
        $params = [
            (int)$customerId,
        ];
        if ($days) {
            $sql .= ' AND DATEDIFF(CURDATE(), created_at) <= ?';
            $params[] = (int)$days;
        }

        return (float)$resource->getConnection()->fetchOne($sql, $params);
    }
}
