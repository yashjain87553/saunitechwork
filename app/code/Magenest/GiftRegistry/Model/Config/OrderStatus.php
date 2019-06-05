<?php
/**
 * Created by PhpStorm.
 * User: ninhvu
 * Date: 09/08/2018
 * Time: 08:18
 */

namespace Magenest\GiftRegistry\Model\Config;
use Magento\Framework\Option\ArrayInterface;


class OrderStatus implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => "Ordered", 'label' => __('Ordered')],
            ['value' => "Invoiced", 'label' => __('Invoiced')],
            ['value' => "new", 'label' => __('New')],
            ['value' => "processing", "label" => __("Processing")],
            ['value' => "closed", "label" => __("Closed")],
            ['value' => "holded", "label" => __("Holded")],
            ['value' => "complete", "label" => __("Complete")],
        ];
    }
}
