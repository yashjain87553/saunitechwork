<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 04/05/2016
 * Time: 15:55
 */
namespace Magenest\GiftRegistry\Model\Email;

use Psr\Log\LoggerInterface;

/**
 * Class Mail
 * @package Magenest\GiftRegistry\Model\Email
 */
class Mail
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $_itemFactory;
    /**
     * @var
     */
    protected $_product;
    
    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $_review;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    protected $_logger;

    /**
     * Mail constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    ) {
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }

    /**
     * @param $recipient
     * @param $subject
     * @param $message
     */
    public function sendMail($recipient, $subject, $message)
    {
            $template_id = $this->_scopeConfig->getValue(
                'giftregistry/email/email_template_share_email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

        if (!$template_id) {
            $this->messageManager->addErrorMessage(__('No email template selected.'));
            return;
        }
        try {
            $this->inlineTranslation->suspend();

            $transport = $this->_transportBuilder->setTemplateIdentifier($template_id)->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )->setTemplateVars(
                [
                    'subject' => $subject,
                    'message' => $message
                ]
            )->setFrom(
                $this->_scopeConfig->getValue(
                    'giftregistry/email/sender',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->addTo(
                $recipient,
                'friends'
            )->getTransport();
            $this->_logger->debug(print_r($recipient, true));
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addNotice('Please check email !', $e);
            $this->_logger->debug($e);
        }
    }
}
