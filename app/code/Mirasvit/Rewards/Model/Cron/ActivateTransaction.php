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



namespace Mirasvit\Rewards\Model\Cron;

use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mirasvit\Rewards\Api\Data\TransactionInterface;
use Mirasvit\Rewards\Helper\Mail;
use Mirasvit\Rewards\Model\ResourceModel\Transaction as TransactionResource;
use Mirasvit\Rewards\Model\ResourceModel\Transaction\Collection as TransactionCollection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ActivateTransaction extends AbstractCron
{
    private $date;
    private $rewardsMail;
    private $transactionCollection;
    private $transactionResource;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Mail $rewardsMail,
        TransactionResource $transactionResource,
        TransactionCollection $transactionCollection,
        DateTime $date,
        Filesystem $filesystem
    ) {
        parent::__construct($filesystem);

        $this->date                  = $date;
        $this->rewardsMail           = $rewardsMail;
        $this->transactionResource   = $transactionResource;
        $this->transactionCollection = $transactionCollection;
    }

    /**
     * @return void
     */
    protected function execute()
    {
        $today = $this->date->gmtDate('Y-m-d 23-59-59', $this->date->gmtTimestamp());

        $inactiveTransactions = $this->transactionCollection->addInActivatedFilter();
        $inactiveTransactions->addFieldToFilter(TransactionInterface::KEY_ACTIVATED_AT, ['lteq' => $today]);

        if (!$inactiveTransactions->count()) {
            echo "No transaction to activate.".PHP_EOL;
            return;
        }

        /** @var \Mirasvit\Rewards\Model\Transaction $transaction */
        foreach ($inactiveTransactions as $transaction) {
            $transaction->setIsActivated(1);
            $this->transactionResource->save($transaction);
            $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction);
        }
        echo "Transactions were activated: " . $inactiveTransactions->count() . PHP_EOL;
    }
}
