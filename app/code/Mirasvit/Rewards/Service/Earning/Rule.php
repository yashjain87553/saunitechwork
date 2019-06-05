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



namespace Mirasvit\Rewards\Service\Earning;

use Mirasvit\Rewards\Api\Data\TierInterface;

class Rule implements \Mirasvit\Rewards\Api\Service\Earning\RuleInterface
{
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->customerFactory = $customerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerTierOptions($rule, $customerId)
    {
        $tiers = $rule->getTiersSerialized();
        $customer = $this->customerFactory->create();
        $customer->getResource()->load($customer, $customerId);

        return $tiers[$customer->getData(TierInterface::CUSTOMER_KEY_TIER_ID)];
    }
}