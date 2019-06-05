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


namespace Mirasvit\Rewards\Model\Checkout;

/**
 * Class TotalsInformationManagement
 */
class Rewards implements \Mirasvit\Rewards\Api\RewardsInterface
{
    private $request;
    private $quoteRepository;
    private $rewardsDataFactory;
    private $rewardsBalance;
    private $rewardsData;
    private $rewardsPurchase;
    private $rewardsCheckout;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Mirasvit\Rewards\Model\Data\RewardsFactory $rewardsDataFactory,
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        \Mirasvit\Rewards\Helper\Checkout $rewardsCheckout,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase
    ) {
        $this->request            = $request;
        $this->quoteRepository    = $quoteRepository;
        $this->rewardsDataFactory = $rewardsDataFactory;
        $this->rewardsBalance     = $rewardsBalance;
        $this->rewardsData        = $rewardsData;
        $this->rewardsPurchase    = $rewardsPurchase;
        $this->rewardsCheckout    = $rewardsCheckout;
    }

    /**
     * {@inheritdoc}
     */
    public function update($shippingCarrier = '', $shippingMethod = '')
    {
        $result = [];
        $result['chechoutRewardsIsShow']         = 0;
        $result['chechoutRewardsPoints']         = 0;
        $result['chechoutRewardsPointsMax']      = 0;
        $result['chechoutRewardsPointsSpend']    = 0;
        $result['chechoutRewardsPointsAvailble'] = 0;
        if (($purchase = $this->rewardsPurchase->getPurchase()) && $purchase->getQuote()->getCustomerId()) {
            if (!$purchase->getQuote()->getIsVirtual() &&
                empty(trim($purchase->getQuote()->getShippingAddress()->getShippingMethod(), '_')) &&
                !empty($shippingCarrier) && !empty($shippingMethod)
            ) {
                $purchase->getQuote()->getShippingAddress()->setCollectShippingRates(true)->setShippingMethod(
                    $shippingCarrier . '_' . $shippingMethod
                );
                $purchase->getQuote()->setCartShippingCarrier($shippingCarrier);
                $purchase->getQuote()->setCartShippingMethod($shippingMethod);
            }
            $purchase->refreshPointsNumber(true);
            if ($purchase->getEarnPoints()) {
                $result['chechoutRewardsPoints'] = $this->rewardsData->formatPoints($purchase->getEarnPoints());
            }
            if ($point = $purchase->getSpendPoints()) {
                $result['chechoutRewardsPointsSpend'] = $this->rewardsData->formatPoints($point);
                $result['chechoutRewardsPointsUsed']  = $point;
            }
            $quote = $purchase->getQuote();
            $result['chechoutRewardsPointsAvailble'] = $this->rewardsData->formatPoints(
                $purchase->getCustomerBalancePoints($quote->getCustomerId())
            );
            $result['chechoutRewardsPointsMax'] = $purchase->getMaxPointsNumberToSpent();
            $result['chechoutRewardsIsShow']    = (bool)$result['chechoutRewardsPointsMax'];
        }

        $rewards = $this->rewardsDataFactory->create();
        $rewards->setData($result);

        return $rewards;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($cartId, $pointsAmount)
    {
        $purchase = $this->rewardsPurchase->getByQuote($cartId);
        if (empty($purchase->getQuote()) || !is_object($purchase->getQuote())) {
            /* @var $quote \Magento\Quote\Model\Quote */
            $quote = $this->quoteRepository->getActive($cartId);
            $purchase->setQuote($quote);
        }
        $this->request->setParams(['points_amount' => $pointsAmount]);

        return $this->rewardsCheckout->processApiRequest($purchase)['success'];
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance($customerId)
    {
        return $this->rewardsBalance->getBalancePoints($customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getBalances()
    {
        return $this->rewardsBalance->getAllBalances();
    }
}
