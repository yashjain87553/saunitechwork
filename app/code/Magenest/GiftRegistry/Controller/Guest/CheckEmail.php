<?php
/**
 * Created by PhpStorm.
 * User: trongphung
 * Date: 21/06/2017
 * Time: 11:15
 */
namespace Magenest\GiftRegistry\Controller\Guest;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magenest\GiftRegistry\Model\Email\Mail;
use Psr\Log\LoggerInterface;

/***
 * Class CheckEmail
 * @package Magenest\GiftRegistry\Controller\Guest
 */
class CheckEmail extends AbstractAccount
{
    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Mail
     */
    protected $_shareMail;

    /**
     * CheckEmail constructor.
     * @param Context $context
     * @param ResultJsonFactory $resultJsonFactory
     * @param LoggerInterface $loggerInterface
     * @param Mail $mail
     */
    public function __construct(
        Context $context,
        ResultJsonFactory $resultJsonFactory,
        LoggerInterface $loggerInterface,
        Mail $mail
    ) {
        $this->_shareMail = $mail;
        $this->_logger=$loggerInterface;
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $data = $this->getDataJson();

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($data);
    }

    /**
     * send mail registry to all friends
     *
     * @return array
     */
    public function getDataJson()
    {
        $data = $this->getRequest()->getPostValue();

        $description = 'Done';
        $error = false;
        
        
        $emails = explode(',', $data['recipient']);

        foreach ($emails as $email) {
            $email_string = str_replace(' ', '', $email);
            $email = $email_string;
        }

        $title = $data['email_subject'];

        $linkRegistry = $data['message'];

        try {
            $this->_shareMail->sendMail($emails, $title, $linkRegistry);
            $this->messageManager->addSuccess('Send all emails complete!');
        } catch (\Exception $e) {
            $error = true;
            $description = $e;
            $this->messageManager->addError($e);
        }

        $params = array();

        $params['description'] = $description;
        $params['error'] = $error;
        return $params;
    }
}
