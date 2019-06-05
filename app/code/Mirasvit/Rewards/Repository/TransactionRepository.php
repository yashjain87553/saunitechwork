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



namespace Mirasvit\Rewards\Repository;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Mirasvit\Rewards\Model\Transaction;

class TransactionRepository implements \Mirasvit\Rewards\Api\Repository\TransactionRepositoryInterface
{
    use \Mirasvit\Rewards\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rewards\Repository\RepositoryFunction\GetList;

    private $transactionCollectionFactory;
    private $objectFactory;
    private $transactionResource;
    private $searchResultsFactory;

    /**
     * @var Transaction[]
     */
    protected $instances = [];

    public function __construct(
        \Mirasvit\Rewards\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        \Mirasvit\Rewards\Model\TransactionFactory $objectFactory,
        \Mirasvit\Rewards\Model\ResourceModel\Transaction $transactionResource,
        \Mirasvit\Rewards\Api\Data\TransactionSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->objectFactory                = $objectFactory;
        $this->transactionResource          = $transactionResource;
        $this->searchResultsFactory         = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rewards\Api\Data\TransactionInterface $transaction)
    {
        $this->transactionResource->save($transaction);

        return $transaction;
    }

    /**
     * {@inheritdoc}
     */
    public function get($transactionId)
    {
        if (!isset($this->instances[$transactionId])) {
            /** @var Transaction $transaction */
            $transaction = $this->objectFactory->create();
            $transaction->load($transactionId);
            if (!$transaction->getId()) {
                throw NoSuchEntityException::singleField('id', $transactionId);
            }
            $this->instances[$transactionId] = $transaction;
        }

        return $this->instances[$transactionId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rewards\Api\Data\TransactionInterface $transaction)
    {
        try {
            $transactionId = $transaction->getId();
            $this->transactionResource->delete($transaction);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete transaction with id %1',
                    $transaction->getId()
                ),
                $e
            );
        }
        unset($this->instances[$transactionId]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($transactionId)
    {
        $transaction = $this->get($transactionId);

        return $this->delete($transaction);
    }

//    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
//    {
//        $collection = $this->getCollection();
//
//        if (null === $searchCriteria) {
//            $searchCriteria = $this->searchCriteriaBuilder->create();
//        } else {
//            $this->collectionProcessor->process($searchCriteria, $collection);
//        }
//
//        /** @var StockSearchResultsInterface $searchResult */
//        $searchResult = $this->stockSearchResultsFactory->create();
//        $searchResult->setItems($collection->getItems());
//        $searchResult->setTotalCount($collection->getSize());
//        $searchResult->setSearchCriteria($searchCriteria);
//        return $searchResult;
//    }

    /**
     * Validate transaction process
     *
     * @param  Transaction $transaction
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFoRewardslParameter)
     */
    protected function validateTransaction(Transaction $transaction)
    {

    }

    /**
     * @return \Mirasvit\Rewards\Model\ResourceModel\Transaction\Collection
     */
    public function getCollection()
    {
        return $this->transactionCollectionFactory->create();
    }
}
