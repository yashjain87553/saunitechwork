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



namespace Mirasvit\Rewards\Block\Notification;

use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * Class Message
 *
 * Displays Rewards Notifications Rules messages
 *
 * @package Mirasvit\Rewards\Block\Notification
 */
class Message extends \Magento\Framework\View\Element\Messages
{
    private $messageHelper;
    private $earnOutput;
    private $rewardsPurchase;
    private $context;

    public function __construct(
        \Mirasvit\Rewards\Helper\Message $messageHelper,
        \Mirasvit\Rewards\Helper\Output\Earn $earnOutput,
        \Mirasvit\Rewards\Helper\Rule\Notification $rewardsPurchase,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        array $data = []
    ) {
        $this->messageHelper      = $messageHelper;
        $this->earnOutput = $earnOutput;
        $this->rewardsPurchase    = $rewardsPurchase;
        $this->context            = $context;
        $this->messageManager     = $messageManager;

        parent::__construct(
            $context,
            $messageFactory,
            $collectionFactory,
            $messageManager,
            $interpretationStrategy,
            $data
        );
    }

    /**
     * @return int
     */
    public function getProductPoints()
    {
        return $this->earnOutput->getProductPoints($this->getProduct());
    }

    /**
     * @var bool|array
     */
    protected $rules = false;

    /**
     * @return array|bool
     */
    public function getRules()
    {
        if (!$this->rules) {
            $this->rules = $this->rewardsPurchase->calcNotificationRules();
        }

        return $this->rules;
    }

    /**
     * @param \Mirasvit\Rewards\Model\Notification\Rule $rule
     *
     * @return string
     */
    public function getMessage($rule)
    {
        if ($rule->getMessage()) {
            return $this->messageHelper->processNotificationVariables($rule->getMessage());
        } else {
            return '';
        }
    }
}
