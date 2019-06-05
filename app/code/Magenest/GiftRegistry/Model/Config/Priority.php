<?php
/**
 * Created by PhpStorm.
 * User: canhnd
 * Date: 22/06/2017
 * Time: 15:47
 */
namespace Magenest\GiftRegistry\Model\Config;

/**
 * Class Priority
 * @package Magenest\GiftRegistry\Model\Config
 */
class Priority implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            0 => 'Like to have',
            1 => 'Love to have',
            2 => 'Must have'
        ];
    }
}
