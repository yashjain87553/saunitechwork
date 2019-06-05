<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 29/12/2015
 * Time: 16:13
 */
namespace Magenest\GiftRegistry\Controller;

/**
 * Interface GiftRegistryProviderInterface
 * @package Magenest\GiftRegistry\Controller
 */
interface GiftRegistryProviderInterface
{
    public function getGiftRegistry($id = null);
}
