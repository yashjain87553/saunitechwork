<?php
/**
 * Created by PhpStorm.
 * User: ninhvu
 * Date: 12/09/2018
 * Time: 17:25
 */

namespace Magenest\GiftRegistry\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '102.2.1') < 0) {
            $setup->getConnection()->addForeignKey(
                $setup->getFkName(
                    'magenest_giftregistry_item',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                $setup->getTable('magenest_giftregistry_item'),
                "product_id",
                $setup->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        }

        if (version_compare($context->getVersion(), '102.2.2') < 0) {
            $this->updateUpdatedTimeField($setup);
        }
        if (version_compare($context->getVersion(), '102.2.3') < 0) {
            $this->updateIsExpired($setup);
        }
        if (version_compare($context->getVersion(), '102.2.5') < 0) {
            $this->updateDateField($setup);
        }
        $setup->endSetup();
    }

    private function updateUpdatedTimeField(SchemaSetupInterface $setup){
        $data = [
            'magenest_giftregistry' => 'updated_at',
            'magenest_giftregistry_registrant' => 'updated_time'
        ];
        foreach ($data as $table => $column) {
            $setup->getConnection()->modifyColumn(
                $setup->getTable($table),
                $column,
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'default' => Table::TIMESTAMP_INIT_UPDATE
                ]
            );
        }
    }

    private function updateIsExpired($setup)
    {
        //add new column
        $setup->getConnection()->addColumn(
            $setup->getTable('magenest_giftregistry'),
            'is_expired',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'nullable' => true,
                'comment' => 'is_gift_registry_expired'
            ]
        );

        //config value old gift registry
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('magenest_giftregistry');

        //Update Data into table
        $sql = "Update " . $tableName . " Set is_expired = 1";
        $connection->query($sql);

        $yesterday = date("Y-m-d 23:59:59", time() - 60 * 60 * 24);
        $sql = "Update " . $tableName . " Set is_expired = 0 where  date > '".$yesterday."'";
        $connection->query($sql);


    }

    private function updateDateField($setup)
    {
        $table = 'magenest_giftregistry';
        $setup->getConnection()->modifyColumn(
            $setup->getTable($table),
            'date',
            [
                'type' => Table::TYPE_DATE,
            ]
        );
    }
}