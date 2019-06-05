<?php

/**
 * Created by Magenest.
 * User: trongpq
 * Date: 12/2/17
 * Time: 01:24
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Plugin\Customer\Account;

/**
 * Class LoginPost
 * @package Magenest\GiftRegistry\Plugin\Customer\Account
 */
class LoginPost
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Magento\Framework\UrlInterface $url
     */

    /**
     * LoginPost constructor.
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $url
    ) {
    
        $this->_session = $session;
        $this->_coreRegistry = $registry;
        $this->url = $url;
    }

    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        $resultRedirect
    ) {
        $registryLogin = $this->_session->getRegistryLogin('registry_login');
        if ($registryLogin != null && $registryLogin) {
            $type = $this->_session->getRegistryType('type');
            $this->_session->setRegistryLogin(false);
            $this->_session->setRegistryType(null);
            $resultRedirect->setUrl($this->url->getUrl('giftregistrys/index/showgift', ['type' => $type]));
            return $resultRedirect;
        }
        return $resultRedirect;
    }
}
