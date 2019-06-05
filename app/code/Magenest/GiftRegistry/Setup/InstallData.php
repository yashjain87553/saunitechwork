<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 13/03/2016
 * Time: 00:27
 */

namespace Magenest\GiftRegistry\Setup;

use Magento\Customer\Helper\Address;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Magenest\GiftRegistry\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        $connection->insert(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['event_type' => 'babygift','event_title'=> 'Baby Gift', 'status' => 1]
        );
        $connection->insert(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['event_type' => 'weddinggift','event_title'=> 'Wedding Gift','status' => 1]
        );
        $connection->insert(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['event_type' => 'birthdaygift','event_title'=> 'Birthday Gift', 'status' => 1]
        );
        $connection->insert(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['event_type' => 'christmasgift','event_title'=> 'Christmas Gift', 'status' => 1]
        );
        $setup->endSetup();
    }
}
