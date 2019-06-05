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

use Magento\Customer\Model\Customer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;
use Mirasvit\Rewards\Model\TierFactory;
use Mirasvit\Rewards\Helper\Json as jsonHelper;
use Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory as EarningCollectionFactory;
use Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\CollectionFactory as SpendingCollectionFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    public function __construct(
        jsonHelper $jsonHelper,
        EarningCollectionFactory $earningCollectionFactory,
        SpendingCollectionFactory $spendingCollectionFactory,
        TierFactory $tierFactory,
        StoreManagerInterface $storeManager,
        IndexerRegistry $indexerRegistry,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig
    ) {
        $this->jsonHelper      = $jsonHelper;
        $this->tierFactory     = $tierFactory;
        $this->storeManager    = $storeManager;
        $this->indexerRegistry = $indexerRegistry;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig       = $eavConfig;

        $this->earningCollectionFactory = $earningCollectionFactory;
        $this->spendingCollectionFactory = $spendingCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                Customer::ENTITY,
                'rewards_subscription',
                [
                    'type'       => 'int',
                    'label'      => 'Subscription to Points Expiring Notification',
                    'input'      => 'select',
                    'source'     => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'required'   => true,
                    'default'    => '1',
                    'sort_order' => 200,
                    'system'     => false,
                    'position'   => 200
                ]
            );
            $subscription = $this->eavConfig->getAttribute(Customer::ENTITY, 'rewards_subscription');
            $subscription->setData(
                'used_in_forms',
                ['adminhtml_customer']
            );
            $subscription->getResource()->save($subscription);
        }

        if (version_compare($context->getVersion(), '1.0.16') < 0) {
            include_once 'Upgrade_Data_1_0_16.php';

            $upgrade = new Upgrade_Data_1_0_16($this->jsonHelper, $this->earningCollectionFactory,
                $this->spendingCollectionFactory, $this->tierFactory, $this->storeManager,
                $this->eavSetupFactory, $this->indexerRegistry, $this->eavConfig);
            $upgrade->upgrade($setup, $context);
        }

        if (version_compare($context->getVersion(), '1.0.17') < 0) {
            include_once 'Upgrade_Data_1_0_17.php';

            $upgrade = new Upgrade_Data_1_0_17($this->indexerRegistry, $this->eavConfig);
            $upgrade->upgrade($setup, $context);
        }

        if (version_compare($context->getVersion(), '1.0.19') < 0) {
            include_once 'Upgrade_Data_1_0_19.php';

            $upgrade = new Upgrade_Data_1_0_19($this->indexerRegistry, $this->eavConfig);
            $upgrade->upgrade($setup, $context);
        }

        if (version_compare($context->getVersion(), '1.0.20') < 0) {
            include_once 'Upgrade_Data_1_0_20.php';

            $upgrade = new Upgrade_Data_1_0_20();
            $upgrade->upgrade($setup, $context);
        }

        $setup->endSetup();
    }
}
