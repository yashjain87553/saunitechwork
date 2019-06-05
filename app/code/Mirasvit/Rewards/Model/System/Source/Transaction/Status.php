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



namespace Mirasvit\Rewards\Model\System\Source\Transaction;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public static function toArray()
    {
        $result = [
            \Mirasvit\Rewards\Model\Transaction::STATUS_PENDING => __('Pending'),
            \Mirasvit\Rewards\Model\Transaction::STATUS_APPROVED => __('Approved'),
            \Mirasvit\Rewards\Model\Transaction::STATUS_DISCARDED => __('Discarded'),
        ];

        return $result;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->toArray() as $k => $v) {
            $result[] = ['value' => $k, 'label' => $v];
        }

        return $result;
    }
}
