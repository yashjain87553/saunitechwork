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


namespace Mirasvit\Rewards\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Upgrade_1_0_18 implements UpgradeInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rewards_earning_rule_customer_group'),
            'customer_group_id',
            'customer_group_id',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 5,
                'unsigned' => false,
                'nullable' => false,
                'comment'  => 'Customer Group Id'
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rewards_notification_rule_customer_group'),
            'customer_group_id',
            'customer_group_id',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 5,
                'unsigned' => false,
                'nullable' => false,
                'comment'  => 'Customer Group Id'
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rewards_spending_rule_customer_group'),
            'customer_group_id',
            'customer_group_id',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 5,
                'unsigned' => false,
                'nullable' => false,
                'comment'  => 'Customer Group Id'
            ]
        );
    }
}