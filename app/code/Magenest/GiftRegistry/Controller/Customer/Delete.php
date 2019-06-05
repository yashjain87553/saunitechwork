<?php
/**
 * Created by PhpStorm.
 * User: canhnd
 * Date: 23/06/2017
 * Time: 10:51
 */
namespace Magenest\GiftRegistry\Controller\Customer;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Psr\Log\LoggerInterface;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\Item\OptionFactory;

/***
 * Class Delete
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class Delete extends AbstractAccount
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
     * @var RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var GiftRegistryFactory
     */
    protected $_giftregistryFactory;

    protected $_optionFactory;

    /**
     * Delete constructor.
     * @param Context $context
     * @param ResultJsonFactory $resultJsonFactory
     * @param LoggerInterface $loggerInterface
     */
    public function __construct(
        Context $context,
        ResultJsonFactory $resultJsonFactory,
        LoggerInterface $loggerInterface,
        RegistrantFactory $registrantFactory,
        ItemFactory $itemFactory,
        GiftRegistryFactory $giftRegistryFactory,
        OptionFactory $optionFactory
    ) {
        $this->_registrantFactory = $registrantFactory;
        $this->_giftregistryFactory = $giftRegistryFactory;
        $this->_itemFactory = $itemFactory;
        $this->_logger=$loggerInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_optionFactory = $optionFactory;
        parent::__construct($context);
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
        $error = true;

        $gifts = $this->_giftregistryFactory->create()
            ->getCollection()
            ->addFieldToFilter('gift_id', $data['gift_id'])
            ->getData();

        if ($gifts) {
            try {
                foreach ($gifts as $gift) {
                    $registrants = $this->_registrantFactory->create()->getCollection()
                        ->addFieldToFilter('giftregistry_id', $gift['gift_id']);
                    $items = $this->_itemFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('gift_id', $gift['gift_id'])
                        ->getData();

                    if ($items) {
                        foreach ($items as $item) {
                            $giftItems = $this->_itemFactory->create()->load($item['gift_item_id']);
                            $options = $this->_optionFactory->create()->getCollection()->addFieldToFilter('gift_item_id', $item['gift_item_id']);
                            foreach ($options as $option) {
                                $option->delete();
                            }
                            $giftItems->delete()->save();
                        }
                    }

                    if ($registrants) {
                        foreach ($registrants as $registrant) {
                            $registrant->delete()->save();
                        }
                    }

                    $queryRegistry = $this->_giftregistryFactory->create()->load($gift['gift_id']);
                    $queryRegistry->delete()->save();
                }
            } catch (\Exception $e) {
                $error = false;
                $description = $e;
                $this->messageManager->addError($e);
            }
        }

        $params = [
            'description' => $description,
            'error' => $error,
        ];

        if ($error) {
            $this->messageManager->addSuccess('Delete gift registry complete.');
        }
        
        return $params;
    }
}
