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
use Mirasvit\Rewards\Api\Data\TierInterface;

class Upgrade_Data_1_0_19 implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Tier\Collection
     */
    protected $tiers;

    /**
     * @var \Mirasvit\Rewards\Helper\Balance
     */
    protected $balanceHelper;

    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Tier\CollectionFactory
     */
    protected $tierCollectionFactory;

    public function __construct(
        IndexerRegistry $indexerRegistry,
        Config $eavConfig
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->eavConfig       = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $subscription = $this->eavConfig->getAttribute(Customer::ENTITY, TierInterface::CUSTOMER_KEY_TIER_ID);
        $subscription->setData('source_model', 'Magento\Eav\Model\Entity\Attribute\Source\Boolean');
        $subscription->getResource()->save($subscription);

        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
        $this->eavConfig->clear();
        $setup->endSetup();
    }
}
