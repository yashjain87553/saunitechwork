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



namespace Mirasvit\Rewards\Model\Notification\Rule\Action;

class Collection extends \Magento\Rule\Model\Action\Collection
{

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Rule\Model\ActionFactory $actionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Rule\Model\ActionFactory $actionFactory,
        array $data = []
    ) {
        parent::__construct($assetRepo, $layout, $actionFactory, $data);
        /* @noinspection PhpUndefinedMethodInspection */
        $this->setType('rewards/notification_rule_action_collection');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $actions = parent::getNewChildSelectOptions();
        $actions = array_merge_recursive($actions, [
            [
                'value' => 'rewards/notification_rule_action_product',
                'label' => __('Update the Product'), ],
        ]);

        return $actions;
    }
}
