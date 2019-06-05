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



namespace Mirasvit\Rewards\Helper\Tier;

use Mirasvit\Rewards\Api\Data\TierInterface;

class Rule extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Mirasvit\Rewards\Api\Repository\TierRepositoryInterface $tierRepository,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->customerFactory = $customerFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->tierRepository = $tierRepository;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * @param \Mirasvit\Rewards\Model\Spending\Rule $rule
     * @param \Magento\Customer\Model\Customer $customer
     * @return array
     */
    public function getCurrentTierData($rule, $customer)
    {
        if (!method_exists($customer, 'getAttributes')) {
            if ($this->customer) {
                $customer = $this->customer;
            } else {
                $customerId = $customer->getId();
                $customer = $this->customerFactory->create();
                $customer->getResource()->load($customer, $customerId);
                $this->customer = $customer;
            }
        }
        $currentTier = (int)$customer->getData(TierInterface::CUSTOMER_KEY_TIER_ID);
        $tears = $rule->getTiersSerialized();
        if ($currentTier) {
            if (isset($tears[$currentTier])) {
                $tierData = $tears[$currentTier];
            } else {
                $tierData = $rule->getDefaultTierData();
            }
        } else {
            $tierData = array_shift($tears);
        }

        return $tierData;
    }

}
