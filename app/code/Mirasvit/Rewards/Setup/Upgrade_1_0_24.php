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

class Upgrade_1_0_24 implements UpgradeInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_transaction'),
            'activated_at',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'length'   => '512',
                'nullable' => true,
                'default'  => null,
                'comment'  => 'Date when transaction will be activated'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_transaction'),
            'is_activated',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length'   => '1',
                'nullable' => false,
                'default'  => 1,
                'comment'  => 'Is transaction activated'
            ]
        );
    }
}