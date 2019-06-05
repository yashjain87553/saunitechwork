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


namespace Mirasvit\Rewards\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\State as AppState;
use Mirasvit\Rewards\Api\Data\TierInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mail extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $appState;

    protected $emailTemplateFactory;
    protected $config;
    protected $rewardsData;
    protected $storeManager;
    protected $assetRepo;
    protected $filesystem;
    protected $context;
    protected $inlineTranslation;
    protected $transportBuilder;
    protected $resource;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Storeview $helpdeskStoreview,
        AppState $appState,
        \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->helpdeskStoreview = $helpdeskStoreview;
        $this->appState = $appState;
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->transportBuilder = $transportBuilder;
        $this->config = $config;
        $this->rewardsData = $rewardsData;
        $this->storeManager = $storeManager;
        $this->assetRepo = $assetRepo;
        $this->filesystem = $filesystem;
        $this->inlineTranslation = $inlineTranslation;
        $this->context = $context;
        $this->resource = $resource;
        parent::__construct($context);
    }

    /**
     * @var array
     */
    public $emails = [];

    /**
     * @return \Mirasvit\Rewards\Model\Config
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @param \Magento\Store\Model\Store $store
     * @return string
     */
    protected function getSender($store = null)
    {
        return $this->config->getNotificationSenderEmail($store);
    }

    /**
     * @param string $templateName
     * @param string $senderName
     * @param string $senderEmail
     * @param string $recipientEmail
     * @param string $recipientName
     * @param array  $variables
     * @param int    $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function send(
        $templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId
    ) {
        // during setup simulate sending
        if ($this->appState->getAreaCode() == 'setup') {
            return true;
        }
        if (!$senderEmail) {
            return false;
        }

        $this->inlineTranslation->suspend();
        $this->transportBuilder
            ->setTemplateIdentifier($templateName)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )
            ->setTemplateVars($variables);

        $message = null;
        if (method_exists($this->transportBuilder, 'getMessage') &&
            strpos(get_class($this->transportBuilder), 'VladimirPopov') === false
        ) {
            $transport = $this->transportBuilder->getTransport();
            $message = $transport->getMessage();
            $message->setFrom($senderEmail, $senderName)
                ->addTo($recipientEmail, $recipientName)
                ->setReplyTo($senderEmail);
        } else {// compatibility with Magento 2.1.x
            $this->transportBuilder
                ->setFrom(
                    [
                        'name' => $senderName,
                        'email' => $senderEmail,
                    ]
                )
                ->addTo($recipientEmail, $recipientName)
                ->setReplyTo($senderEmail);
            $transport = $this->transportBuilder->getTransport();
        }

        try {
            /* @var \Magento\Framework\Mail\Transport $transport */
            $transport->sendMessage();
        } catch (\Exception $e) {

        }
        if ($message) {
            $message->clearFrom();
        }

        $this->inlineTranslation->resume();

        return true;
    }

    /**
     * @param \Mirasvit\Rewards\Model\Transaction $transaction
     * @param bool|false                          $emailMessage
     * @return bool
     */
    public function sendNotificationBalanceUpdateEmail($transaction, $emailMessage = false)
    {
        if ($emailMessage) {
            $emailMessage = $this->parseVariables($emailMessage, $transaction);
        }

        $customer     = $transaction->getCustomer();
        $store        = $customer ? $customer->getData('store_id') : null;
        if ($transaction->getActivatedAt() && !$transaction->getIsActivated()) {
            $templateName = $this->getConfig()->getNotificationTransactionInactiveEmailTemplate($store);
        } else {
            $templateName = $this->getConfig()->getNotificationBalanceUpdateEmailTemplate($store);
        }
        if ($templateName == 'none' || !$customer || !$this->isCustomerSubscribed($customer)) {
            return false;
        }
        if ($store) {
            $storeId = $store;
        } else {
            $storeId = $customer->getStore()->getId();
        }

        $recipientName  = $customer->getName();
        $recipientEmail = $customer->getEmail();
        $this->rewardsData->setCurrentStore($customer->getStore());
        $variables = [
            'customer'              => $customer,
            'store'                 => $this->storeManager->getStore($storeId),
            'transaction'           => $transaction,
            'transaction_days_left' => $transaction->getDaysLeft(),
            'transaction_amount'    => $this->rewardsData->formatPoints($transaction->getAmount(), $storeId),
            'transaction_comment'   => $transaction->getComment(),
            'activation_days'       => $this->getConfig()->getGeneralActivatesAfterDays(),
            'balance_total'         => $this->rewardsData->formatPoints($this->getBalancePoints($customer), $storeId),
            'message'               => $this->rewardsData->convertToHtml($emailMessage),
            'no_message'            => $emailMessage == false || $emailMessage == '',
        ];

        $senderName = $this->context->getScopeConfig()->getValue(
            "trans_email/ident_{$this->getSender($customer->getStore())}/name",
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $senderEmail = $this->context->getScopeConfig()->getValue(
            "trans_email/ident_{$this->getSender($customer->getStore())}/email",
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId);
    }

    /**
     * @param \Mirasvit\Rewards\Model\Tier $tier
     * @param \Magento\Customer\Model\Customer $customer
     * @return bool
     */
    public function sendNotificationTierUpEmail($tier, $customer)
    {
        $storeId = $customer->getData('store_id') ?: $customer->getStore()->getId();
        $tier->setStoreId($storeId);
        $templateName = $this->helpdeskStoreview->getStoreViewValue($tier, TierInterface::KEY_TEMPLATE_ID);
        if (!$templateName) {
            $templateName = 'rewards_email_tier_up';
        }
        if ($templateName == 'none') {
            return false;
        }
        $recipientName  = $customer->getName();
        $recipientEmail = $customer->getEmail();
        $variables = [
            'customer'      => $customer,
            'store'         => $this->storeManager->getStore($storeId),
            'tier'          => $tier,
            'balance_total' => $this->rewardsData->formatPoints($this->getBalancePoints($customer), $storeId),
            'points_name'   => $this->getConfig()->getGeneralPointUnitName($storeId),
        ];
        $senderName  = $this->context->getScopeConfig()->getValue(
            "trans_email/ident_{$this->getSender($customer->getStore())}/name",
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $senderEmail = $this->context->getScopeConfig()->getValue(
            "trans_email/ident_{$this->getSender($customer->getStore())}/email",
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $this->send(
            $templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId
        );
    }

    /**
     * @param \Mirasvit\Rewards\Model\Transaction $transaction
     * @return bool
     */
    public function sendNotificationPointsExpireEmail($transaction)
    {
        $customer     = $transaction->getCustomer();
        $store        = $customer ? $customer->getData('store_id') : null;
        $templateName = $this->getConfig()->getNotificationPointsExpireEmailTemplate($store);
        if ($templateName == 'none' || !$this->isCustomerSubscribed($customer)) {
            return false;
        }
        if ($store) {
            $storeId = $store;
        } else {
            $storeId = $customer->getStore()->getId();
        }
        $recipientName  = $customer->getName();
        $recipientEmail = $customer->getEmail();
        $transactionAmount = $transaction->getAmount() - $transaction->getAmountUsed();
        $variables = [
            'customer'              => $customer,
            'store'                 => $this->storeManager->getStore($storeId),
            'transaction'           => $transaction,
            'transaction_days_left' => $transaction->getDaysLeft(),
            'transaction_amount'    => $this->rewardsData->formatPoints($transactionAmount, $storeId),
        ];
        $senderName  = $this->context->getScopeConfig()->getValue(
            "trans_email/ident_{$this->getSender($customer->getStore())}/name",
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $senderEmail = $this->context->getScopeConfig()->getValue(
            "trans_email/ident_{$this->getSender($customer->getStore())}/email",
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId);
    }

    /**
     * @param  \Mirasvit\Rewards\Model\Referral $referral
     * @param string                            $message
     * @return bool
     */
    public function sendReferralInvitationEmail($referral, $message)
    {
        $store = $referral ? $referral->getData('store_id') : null;
        $templateName = $this->getConfig()->getReferralInvitationEmailTemplate($store);
        if ($templateName == 'none') {
            return false;
        }
        $recipientEmail = $referral->getEmail();
        $recipientName  = $referral->getName();
        $storeId  = $referral->getStoreId();
        $customer = $referral->getCustomer();
        $variables = [
            'customer'       => $customer,
            'name'           => $referral->getName(),
            'message'        => $message,
            'invitation_url' => $referral->getInvitationUrl(),
        ];
        $senderName = $this->context->getScopeConfig()->getValue(
            "trans_email/ident_{$this->getSender($customer->getStore())}/name",
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $senderEmail = $this->context->getScopeConfig()->getValue(
            "trans_email/ident_{$this->getSender($customer->getStore())}/email",
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId);
    }

    /**
     * Can parse template and return ready text.
     *
     * @param string $variable  Text with variables like {{var customer.name}}.
     * @param array  $variables Array of variables.
     * @param int    $storeId
     *
     * @return string - ready text
     */
    public function processVariable($variable, $variables, $storeId)
    {
        $template = $this->emailTemplateFactory->create();
        $template->setDesignConfig([
            'area'  => 'frontend',
            'store' => $storeId,
        ]);
        $template->setTemplateText($variable);
        $html = $template->getProcessedTemplate($variables);

        return $html;
    }

    /**
     * @param string                              $text
     * @param \Mirasvit\Rewards\Model\Transaction $transaction
     * @return string
     */
    public function parseVariables($text, $transaction)
    {
        $customer = $transaction->getCustomer();
        $storeId = $customer->getData('store_id') ?: $customer->getStore()->getId();

        $variables = [
            'customer' => $customer,
            'store' => $this->storeManager->getStore($storeId),
            'transaction' => $transaction,
            'transaction_days_left' => $transaction->getDaysLeft(),
            'transaction_amount' => $this->rewardsData->formatPoints($transaction->getAmount(), $storeId),
            'balance_total' => $this->rewardsData->formatPoints($this->getBalancePoints($customer), $storeId),
        ];
        $text = $this->processVariable($text, $variables, $storeId);

        return $text;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return bool
     */
    public function isCustomerSubscribed($customer)
    {
        // rewards_subscription = 0 - customer set manually email cancellation. Default true.
        return $customer->getData('rewards_subscription') !== 0 && $customer->getData('rewards_subscription') !== '0';
    }

    /**
     * This is a dublicate of function Balance::getBalancePoints
     * we created it because of circular dependency problem
     * need to find a more elegant solution
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return int
     */
    private function getBalancePoints($customer)
    {
        if (is_object($customer)) {
            $customer = $customer->getId();
        }
        $resource = $this->resource;
        $table = $resource->getTableName('mst_rewards_transaction');

        return (int)$resource->getConnection()->fetchOne(
            "SELECT SUM(amount) FROM $table WHERE customer_id=?", [(int)$customer]
        );
    }

}
