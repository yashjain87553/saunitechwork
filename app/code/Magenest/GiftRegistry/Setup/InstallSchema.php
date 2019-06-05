<?php

namespace Magenest\GiftRegistry\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table as Table;

/**
 * Class InstallSchema
 * @package Magenest\GiftRegistry\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('magenest_giftregistry')
        )->addColumn(
            'gift_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Gift Registry ID'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Customer ID'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Title'
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Type'
        )->addColumn(
            'image',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Background Image'
        )->addColumn(
            'location',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Location'
        )->addColumn(
            'date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            255,
            [],
            'Date'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Last updated date'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Description'
        )->addColumn(
            'privacy',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Privacy'
        )->addColumn(
            'password',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Password'
        )->addColumn(
            'show_in_search',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Show In Search'
        )->addColumn(
            'shipping_address',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Shipping Address'
        )->addColumn(
            'gift_options',
            Table::TYPE_TEXT,
            null,
            [],
            'Gift Options'
        )->setComment(
            'Gift Registry main Table'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('magenest_giftregistry_item')
        )->addColumn(
            'gift_item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Gift Registry item ID'
        )->addColumn(
            'gift_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Gift Registry ID'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0', 'nullable' => false],
            'Product ID'
        )->addColumn(
            'product_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Product Name'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Store ID'
        )->addColumn(
            'added_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Add date and time'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Short description of  item'
        )->addColumn(
            'qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '11',
            ['nullable' => false,'unsigned' => true,],
            'Qty'
        )->addColumn(
            'duplicate',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '11',
            ['nullable' => false,'unsigned' => true, 'default' => 0],
            'Duplicate'
        )->addColumn(
            'received_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '11',
            ['nullable' => false,'unsigned' => true,],
            'Received Qty'
        )->addColumn(
            'invoiced_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '11',
            ['nullable' => false,'unsigned' => true,],
            'Invoiced Qty'
        )->addColumn(
            'priority',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '11',
            ['nullable' => false,'unsigned' => true,],
            'Priority'
        )->addColumn(
            'note',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Note'
        )
            ->addColumn(
                'final_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Price'
            )
            ->addColumn(
                'buy_request',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Buyer Request'
            )
            ->setComment(
                'Gift Registry items'
            );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'giftregistry_item_option'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magenest_giftregistry_item_option')
        )->addColumn(
            'option_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Option Id'
        )->addColumn(
            'gift_item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Item Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Product Id of gift registry item'
        )->addColumn(
            'code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Code of the option'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Value Of the Option'
        )->setComment(
            'Gift Registry Item Option Table'
        );
        $installer->getConnection()->createTable($table);

        /* create table magenest_registry_registrant */
        $table = $installer->getConnection()->newTable($installer->getTable('magenest_giftregistry_registrant'))
            ->addColumn(
                'registrant_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Owner ID'
            )
            ->addColumn(
                'email',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Registrant Email'
            )->addColumn(
                'firstname',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'First Name'
            )->addColumn(
                'lastname',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Last Name'
            )->addColumn(
                'giftregistry_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Gift Registry Id which is foreign key of the table'
            )
            ->addColumn(
                'created_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Time created'
            )
            ->addColumn(
                'updated_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Time update'
            )
            ->setComment('Table Gift Registrant');

        $installer->getConnection()->createTable($table);

        /* add table magenest_registry_event_type */
        $table = $installer->getConnection()->newTable($installer->getTable('magenest_giftregistry_event_type'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id Event Type'
            )
            ->addColumn(
                'event_type',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Event Type'
            )
            ->addColumn(
                'event_title',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Event Title'
            )
            ->addColumn(
                'languages',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'For multiple stores'
            )
            ->addColumn(
                'image',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Background Image'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Status'
            )
            ->setComment('Table Gift Registry Type');
        $installer->getConnection()->createTable($table);

        /* add table magenest_registry_order */
        $table = $installer->getConnection()->newTable($installer->getTable('magenest_giftregistry_order'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id Order Registry'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Id Increment'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                null,
                [ 'nullable' => true],
                'Status'
            )
            ->addColumn(
                'giftregistry_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Hold Foreign Key'
            )
            ->setComment('Table Order Registry');
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
