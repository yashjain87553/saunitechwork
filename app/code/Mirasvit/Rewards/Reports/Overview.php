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


namespace Mirasvit\Rewards\Reports;

use Mirasvit\Report\Model\AbstractReport;

class Overview extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Reward Points');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'rewards_overview';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('mst_rewards_points_aggregated_hour');
        $this->addFastFilters([
            'mst_rewards_points_aggregated_hour|period',
            'mst_rewards_points_aggregated_hour|customer_group_id',
            'mst_rewards_points_aggregated_hour|store_id',
        ]);
        $this->setDefaultColumns([
            'mst_rewards_points_aggregated_hour|total_points_earned__sum',
            'mst_rewards_points_aggregated_hour|total_points_spent__sum',
            'mst_rewards_points_aggregated_hour|total_points_spent_in_money__sum',
            'mst_rewards_points_aggregated_hour|total_points_earned__avg',
            'mst_rewards_points_aggregated_hour|total_points_spent__avg',
            'mst_rewards_points_aggregated_hour|total_expired_points__sum',
        ]);

        $this->addColumns([
            'mst_rewards_points_aggregated_hour|period__quarter'
        ]);

        $this->setDefaultDimension('mst_rewards_points_aggregated_hour|period__day');

        $this->addDimensions([
            'mst_rewards_points_aggregated_hour|period__hour',
            'mst_rewards_points_aggregated_hour|period__day',
            'mst_rewards_points_aggregated_hour|period__week',
            'mst_rewards_points_aggregated_hour|period__month',
            'mst_rewards_points_aggregated_hour|period__year',
        ]);

        $this->getChartConfig()
            ->setType('column')
            ->setDefaultColumns([
                'mst_rewards_points_aggregated_hour|total_points_earned__sum',
            ]);
    }

    /**
     * @return mixed|string[]|null
     */
    public function getApplicableColumns()
    {
        return $this->getColumns();
    }

    /**
     * @return mixed|string[]|null
     */
    public function getApplicableDimensions()
    {
        return $this->getPrimaryDimensions();
    }
}