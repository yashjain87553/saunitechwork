<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 7/17/17
 * Time: 1:11 PM
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Search
 * @package Magenest\GiftRegistry\Controller\Index
 */
class Search extends \Magento\Framework\App\Action\Action
{
    const FILTER_BY_TITLE = 1;
    const FILTER_BY_NAME = 2;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory
     */
    protected $_eventFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\Registrant\CollectionFactory
     */
    protected $_registrantFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    protected $_customerFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $eventFactory,
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $customerFactory,
        \Magento\Framework\App\Action\Context $context
    ) {
    
        $this->_logger = $logger;
        $this->_eventFactory = $eventFactory;
        $this->_registrantFactory = $registrantFactory;
        $this->_customerFactory = $customerFactory;
        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $query = $this->getRequest()->getParams();
        $result = null;
        if ($query['filter_selected'] == self::FILTER_BY_TITLE) {
            $title = $query['title'];
            $result = $this->searchByTitle($title, $query['type']);
        } else {
            $firstName = $query['firstName'];
            $lastName = $query['lastName'];
            $result = $this->searchByName($firstName, $lastName, $query['type']);
        }
        $information = [];
        foreach ($result as $registrant) {
            $event = $this->getInforEvent($registrant['giftregistry_id']);
            if($event->getId()) {
                $eventdata = $event->getData();
                $registrant['type'] = $eventdata['type'];
                $registrant['title'] = $eventdata['title'];
                $registrant['location'] = $eventdata['location'];
                $registrant['date'] = $eventdata['date'];
                $registrant['url'] = $this->getViewUrl($eventdata['password'], $eventdata['gift_id'], $eventdata['type']);
                array_push($information, $registrant);
            }
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($information);
        return $resultJson;
    }

    public function searchByTitle($title, $type)
    {
        $title = trim($title);
        $titleResult = $this->_registrantFactory->create()
            ->getCollection();
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        if ($type != "*") {
            $titleResult->getSelect()
                ->join(['registry' => $titleResult->getTable("magenest_giftregistry")], 'giftregistry_id = registry.gift_id')
                ->where("title LIKE ?", "%".$title."%")
                ->where("type = ?", $type);
            return $titleResult->getData();
        }
        $titleResult->getSelect()
            ->join(['registry' => $titleResult->getTable("magenest_giftregistry")], 'giftregistry_id = registry.gift_id')
            ->where("title LIKE ?", "%".$title."%");
        return $titleResult->getData();
    }

    public function searchByName($firstName, $lastName, $type)
    {
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $firstNameResult = $this->_registrantFactory->create()
            ->getCollection()
            ->addFieldToFilter('firstname', ['like' => '%' . $firstName . '%']);
        $lastNameResult = $this->_registrantFactory->create()
            ->getCollection()
            ->addFieldToFilter('lastname', ['like' => '%' . $lastName . '%']);

        if ($type != "*") {
            $firstNameResult->getSelect()
                ->join(['registry' => $firstNameResult->getTable('magenest_giftregistry')], 'main_table.giftregistry_id = registry.gift_id')->where("type=?", $type);
            $lastNameResult->getSelect()
                ->join(['registry' => $lastNameResult->getTable('magenest_giftregistry')], 'main_table.giftregistry_id = registry.gift_id')->where("type=?", $type);
        }

        $finalResult = array();

        if ($lastNameResult) {
            foreach ($lastNameResult->getData() as $lastResult) {
                foreach ($firstNameResult->getData() as $firstResult) {
                    if ($firstResult == $lastResult) {
                        array_push($finalResult, $lastResult);
                        break;
                    }
                }
            }
        }

        return $finalResult;
    }

    /**
     * @param $registryId
     * @return mixed
     */
    public function getInforEvent($registryId)
    {
        $infor = $this->_eventFactory->create()
            ->addFieldToFilter('gift_id', $registryId)
            ->addFieldToFilter('show_in_search', 'on')->getFirstItem();
        if($infor){
            return $infor;
        }
        return null;
    }

    /**
     * @param $event
     * @return string
     */
    public function getViewUrl($event_password, $event_id, $event_type)
    {
        if ($event_password != null) {
            return $this->_url->getUrl('giftregistrys/guest/view/', ['id' => $event_id ,'pass'=>$event_password,'type'=>$event_type]);
        } else {
            return $this->_url->getUrl('giftregistrys/guest/view/', ['id' => $event_id ,'type'=>$event_type]);
        }
    }

    public function searchTypeWedding($type)
    {
        $query = $this->getRequest()->getParams();

        $husbandName = $query['event_hn'];
        $wifeName = $query['event_wn'];

        $listEvent = $this->_eventFactory->create()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('show_in_search', 'on')
            ->getData();

        /**  Result with Husband's Name */
        $resultH = array();
        foreach ($listEvent as $eventH) {
            $option = json_decode($eventH['gift_options']);
            $hName = $option->{'husband_name'};
            if (strpos(strtoupper($hName), strtoupper($husbandName)) !== false) {
                array_push($resultH, $eventH);
            }
        }
        /**  Result with Wife's Name */
        $resultW = array();
        foreach ($listEvent as $eventW) {
            $option = json_decode($eventW['gift_options']);
            $wName = $option->{'wife_name'};
            if (strpos(strtoupper($wName), strtoupper($wifeName)) !== false) {
                array_push($resultW, $eventW);
            }
        }
        /**  Check duplicate value */
        $result = array();

        if ($resultW) {
            foreach ($resultW as $data1) {
                foreach ($resultH as $data2) {
                    if ($data1 == $data2) {
                        array_push($result, $data2);
                        break;
                    }
                }
            }
        }
        return $result;
    }

    public function searchTypeBaby($type)
    {
        $babyName = $this->getBabyName();

        $listEvent = $this->_eventFactory->create()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('show_in_search', 'on')
            ->getData();

        /**  Result with Baby's Name */
        $result = array();

        foreach ($listEvent as $event) {
            $option = json_decode($event['gift_options']);
            $name = $option->{'baby_name'};
            if (strpos(strtoupper($name), strtoupper($babyName)) !== false) {
                array_push($result, $event);
            }
        }

        return $result;
    }
}
