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


namespace Mirasvit\Rewards\Model\Spending;

use Mirasvit\Rewards\Api\Config\Rule\SpendingStyleInterface;
use Mirasvit\Rewards\Api\Data\Spending\RuleInterface;
use Mirasvit\Rewards\Helper\Json as HelperJson;


/**
 * @method \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\Collection getCollection()
 * @method \Mirasvit\Rewards\Model\Spending\Rule load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rewards\Model\Spending\Rule setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rewards\Model\Spending\Rule setIsMassStatus(bool $flag)
 * @method \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule getResource()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rule extends \Magento\SalesRule\Model\Rule
{
    const TYPE_PRODUCT = 'product';
    const TYPE_CART = 'cart';
    const TYPE_CUSTOM = 'custom';

    const CACHE_TAG = 'rewards_spending_rule';

    protected $_cacheTag = 'rewards_spending_rule';
    protected $_eventPrefix = 'rewards_spending_rule';
    private $helperJson;
    private $tierRuleHelper;
    private $spendingRuleConditionCombineFactory;
    private $spendingRuleActionCollectionFactory;
    private $ruleConditionProductCombineFactory;
    private $roundService;
    private $context;
    private $registry;
    private $resource;
    private $resourceCollection;
    private $rewardsStoreview;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        HelperJson $helperJson,
        \Mirasvit\Rewards\Helper\Tier\Rule $tierRuleHelper,
        \Mirasvit\Rewards\Helper\Storeview $rewardsStoreview,
        Rule\Condition\CombineFactory $spendingRuleConditionCombineFactory,
        Rule\Action\CollectionFactory $spendingRuleActionCollectionFactory,
        \Mirasvit\Rewards\Model\Spending\Rule\Condition\Product\CombineFactory $ruleConditionProductCombineFactory,
        \Mirasvit\Rewards\Service\RoundService $roundService,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegenFactory,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helperJson                          = $helperJson;
        $this->tierRuleHelper                      = $tierRuleHelper;
        $this->rewardsStoreview                    = $rewardsStoreview;
        $this->spendingRuleConditionCombineFactory = $spendingRuleConditionCombineFactory;
        $this->spendingRuleActionCollectionFactory = $spendingRuleActionCollectionFactory;
        $this->ruleConditionProductCombineFactory  = $ruleConditionProductCombineFactory;
        $this->roundService                        = $roundService;
        $this->context                             = $context;
        $this->registry                            = $registry;
        $this->resource                            = $resource;
        $this->resourceCollection                  = $resourceCollection;

        parent::__construct($context, $registry, $formFactory, $localeDate, $couponFactory, $codegenFactory,
            $condCombineFactory, $condProdCombineF, $couponCollection, $storeManager, $resource, $resourceCollection,
            $data);
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rewards\Model\ResourceModel\Spending\Rule');
        $this->setIdFieldName('spending_rule_id');
    }

    /**
     * @return string
     */
    public function getFrontName()
    {
        return $this->rewardsStoreview->getStoreViewValue($this, 'front_name');
    }

    /**
     * @param int|string $value
     * @return $this
     */
    public function setFrontName($value)
    {
        $this->rewardsStoreview->setStoreViewValue($this, 'front_name', $value);

        return $this;
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /** Rule Methods **/
    /**
     * @return Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->spendingRuleConditionCombineFactory->create();
    }

    /**
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->ruleConditionProductCombineFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getConditions()
    {
        $condition = null;
        try {
            $condition = parent::getConditions();
        } catch (\Exception $e) {
            if ($serializeObj = $this->getSerializer()) {
                $origin = clone $this->serializer;
                $this->serializer = $serializeObj;
                $condition = parent::getConditions();
                $this->serializer = $origin;
            }
        }

        return $condition;
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        $action = null;
        try {
            $action = parent::getActions();
        } catch (\Exception $e) {
            if ($serializeObj = $this->getSerializer()) {
                $origin = clone $this->serializer;
                $this->serializer = $serializeObj;
                $action = parent::getActions();
                $this->serializer = $origin;
            }
        }

        return $action;
    }

    /**
     * @return bool|\Magento\Framework\Serialize\Serializer\Json
     */
    protected function getSerializer()
    {
        $serializer = false;
        if (class_exists(\Magento\Framework\Serialize\Serializer\Serialize::class)) {
            $serializer = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Serialize\Serializer\Serialize::class
            );
        }

        return $serializer;

    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductIds()
    {
        return $this->_getResource()->getRuleProductIds($this->getId());
    }

    /**
     * @param string $format
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toString($format = '')
    {
        $this->load($this->getId());
        $string = $this->getConditions()->asStringRecursive();

        $string = nl2br(preg_replace('/ /', '&nbsp;', $string));

        return $string;
    }

    /**
     * @return array
     */
    public function getTiersSerialized()
    {
        $result = [];
        $tiers = $this->getData(RuleInterface::KEY_TIERS_SERIALIZED);
        if ($tiers) {
            $result = $this->helperJson->unserialize($tiers);
        }

        return $result;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyAll()
    {
        $this->_getResource()->applyAllRulesForDateRange();
    }

    /**
     * @return array
     */
    public function getWebsiteIds()
    {
        return $this->getData('website_ids');
    }

    /**
     * @return array
     */
    public function getSpendingStyle($customer)
    {
        $tierData = $this->tierRuleHelper->getCurrentTierData($this, $customer);
        return $tierData[RuleInterface::KEY_TIER_KEY_SPENDING_STYLE];
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param float $subtotal
     * @return bool|float
     */
    public function getSpendMinAmount($customer, $subtotal)
    {
        $tierData = $this->tierRuleHelper->getCurrentTierData($this, $customer);
        $min = $tierData[RuleInterface::KEY_TIER_KEY_SPEND_MIN_POINTS];

        // \p{Po} - because % has different presentation for different languages
        if (!preg_match('/\p{Po}/u', $min)) {
            return $min;
        }
        $min = preg_replace('/\p{Po}/u', '', $min);

        return ceil(
            $subtotal * $min / 100 / $this->getMonetaryStep($customer, $subtotal) * $this->getSpendPoints($customer)
        );
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param float $subtotal
     * @return bool|float
     */
    public function getSpendMaxAmount($customer, $subtotal)
    {
        $tierData = $this->tierRuleHelper->getCurrentTierData($this, $customer);
        $tierMax = $tierData[RuleInterface::KEY_TIER_KEY_SPEND_MAX_POINTS];
        $max = $tierMax ?: '100%';
        $monetaryStep = $this->getMonetaryStep($customer, $subtotal);
        $stepPoints = $this->getSpendPoints($customer);

        if (strpos($max, '%') === false) {
            $max = '100%';
        }
        // \p{Po} - because % has different presentation for different languages
        if (preg_match('/\p{Po}/u', $tierMax)) {
            $tierMax = preg_replace('/\p{Po}/u', '', $tierMax);
            $tierMax = (int)($subtotal * $tierMax / 100 / $monetaryStep * $stepPoints);
        }
        $max = str_replace('%', '', $max);

        $points = $subtotal * $max / 100 / $monetaryStep * $stepPoints;
        $points = $this->roundService->round($points, $max == 100);//if points is limited we should not exceed it

        return $tierMax ? min($points, $tierMax) : $points;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return int
     */
    public function getSpendPoints($customer)
    {
        $tierData = $this->tierRuleHelper->getCurrentTierData($this, $customer);
        return $tierData[RuleInterface::KEY_TIER_KEY_SPEND_POINTS];
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param float $subtotal
     * @return bool|float
     */
    public function getMonetaryStep($customer, $subtotal = 0)
    {
        $tierData = $this->tierRuleHelper->getCurrentTierData($this, $customer);
        $value = $tierData[RuleInterface::KEY_TIER_KEY_MONETARY_STEP];
        if (!$subtotal || strpos($value, '%') === false) {
            return $value;
        }
        $value = str_replace('%', '', $value);

        return $subtotal * $value / 100;
    }

    /**
     * @return bool
     */
    public function validateTierFields()
    {
        $tiersData = $this->getTiersSerialized();
        foreach ($tiersData as $tier) {
            $max = $tier[RuleInterface::KEY_TIER_KEY_SPEND_MAX_POINTS];
            $min = $tier[RuleInterface::KEY_TIER_KEY_SPEND_MIN_POINTS];
            // skip validation if one of values contains %
            if ((strpos($max, '%') === false && strpos($min, '%') !== false) ||
                (strpos($min, '%') === false && strpos($max, '%') !== false)
            ) {
                return true;
            }
            if ($max && $min >= $max) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addData(array $data)
    {
        if (!empty($data['front_name']) && !$this->helperJson->isEncoded($data['front_name']) &&
            !$this->helperJson->isSerialized($data['front_name'])
        ) {
            $this->setFrontName($data['front_name']);
            unset($data['front_name']);
        }

        return parent::addData($data);
    }

    /**
     * @return array
     */
    public function getDefaultTierData()
    {
        return [
            RuleInterface::KEY_TIER_KEY_SPENDING_STYLE   => SpendingStyleInterface::STYLE_PARTIAL,
            RuleInterface::KEY_TIER_KEY_MONETARY_STEP    => 0,
            RuleInterface::KEY_TIER_KEY_SPEND_POINTS     => 1,
            RuleInterface::KEY_TIER_KEY_SPEND_MAX_POINTS => 0,
            RuleInterface::KEY_TIER_KEY_SPEND_MIN_POINTS => 0,
        ];
    }
}
