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



namespace Mirasvit\Rewards\Block\Account\Listing;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Model\Transaction;

/**
 * Class ActiveTransactions. Customer account active transaction block
 *
 * @package Mirasvit\Rewards\Block\Account\Listing
 */
class ActiveTransactions extends Template
{
    private $config;

    /**
     * @var Transaction
     */
    protected $transaction;

    public function __construct(
        Config $config,
        Context $context,
        array $data = []
    ) {
        $this->config = $config;

        parent::__construct($context, $data);
    }

    /**
     * @param Transaction $transaction
     *
     * @return $this
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @return string
     */
    public function getExpirationEnabled()
    {
        return $this->config->getGeneralExpiresAfterDays();
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        if ($this->transaction->getAmount() <= 0) {
            return '';
        }
        $expires = $this->transaction->getData('expires_at');
        $date = date_create($expires);
        $dateNow = date_create();
        $diff = date_diff($date, $dateNow);
        $this->transaction->setData('expires_diff', $diff);
        if ($expires && !$diff->invert) {
            return __('Expired');
        }
        return __('Active');
    }

    /**
     * @return string
     */
    public function getStatusDescription()
    {
        if ($this->transaction->getAmount() <= 0) {
            return '';
        }
        $diff = $this->transaction->getData('expires_diff');
        if (!$diff) {
            $this->getStatus();
            $diff = $this->transaction->getData('expires_diff');
        }
        $expires = $this->transaction->getData('expires_at');
        if ($expires) {
            if (!$diff->invert) {
                return __('Points are expired');
            } else {
                return __('Will expire '.$this->transaction->getExpiresAtFormatted());
            }
        }
        return '';
    }
}
