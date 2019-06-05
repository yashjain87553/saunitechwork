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


namespace Mirasvit\Rewards\Ui\Tier\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Model\Config;

class Tier implements ModifierInterface
{
    public function __construct(Config $config, StoreManagerInterface $storeManager)
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
        switch ($this->config->getTierCalcType()) {
            case TierInterface::TYPE_ORDER:
                $label = __('Minimum sum of spent %1', $currencyCode);
                break;
            case TierInterface::TYPE_POINT:
            default:
                $label = __('Minimum points number to reach the tier');
        }
        $meta['general']['children']['min_earn_points']['arguments']['data']['config']['label'] = $label;

        return $meta;
    }
}
