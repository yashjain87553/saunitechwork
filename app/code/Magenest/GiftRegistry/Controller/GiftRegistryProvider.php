<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 29/12/2015
 * Time: 16:15
 */
namespace Magenest\GiftRegistry\Controller;

/**
 * Class GiftRegistryProvider
 * @package Magenest\GiftRegistry\Controller
 */
class GiftRegistryProvider implements GiftRegistryProviderInterface
{
    /**
     * @var \Magenest\GiftRegistry\Model\GiftRegistry
     */
    protected $giftRegistry;

    /**
     * @var \Magenest\GiftRegistry\Model\GiftRegistryFactory
     */
    protected $giftRegistryFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * GiftRegistryProvider constructor.
     * @param \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magenest\GiftRegistry\Model\GiftRegistryFactory $giftRegistryFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
        $this->giftRegistryFactory = $giftRegistryFactory;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @param null $id
     * @return mixed
     */
    public function getGiftRegistry($id = null)
    {
        $customerId = $this->customerSession->getCustomerId();

        $giftRegistry = $this->giftRegistryFactory->create();

        if ($id) {
            $giftRegistry->load($id);
        } else {
            $giftRegistry->loadByCustomerId($customerId);
        }

        return $giftRegistry;
    }
}
