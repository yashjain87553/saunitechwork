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



namespace Mirasvit\Rewards\Block\Checkout\Onepage;

/**
 * Class Magestore
 *
 * Rewards block for Magestore onepage checkout
 *
 * @package Mirasvit\Rewards\Block\Checkout\Onepage
 */
class Magestore extends AbstractOnePageCheckout
{
    public function __construct(
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_template = 'checkout/cart/usepoints_magestore_onestepcheckout.phtml';

        parent::__construct(
            $sessionFactory, $rewardsPurchase, $rewardsBalance, $customerSession, $checkoutSession, $context, $data
        );
    }
}
