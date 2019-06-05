<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 29/04/2016
 * Time: 14:55
 */

namespace Magenest\GiftRegistry\Block\Guest;

/**
 * Class ListSearch
 * @package Magenest\GiftRegistry\Block\Guest
 */
class ListSearch extends \Magento\Framework\View\Element\Template
{
    const FILTER_BY_TITLE = 1;
    const FILTER_BY_NAME = 2;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory
     */
    protected $_eventFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\TypeFactory
     */
    protected $_typeFactory;

    /**
     * ListSearch constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $eventFactory
     * @param \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory
     * @param \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $customerFactory
     * @param \Magenest\GiftRegistry\Model\TypeFactory $typeFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $eventFactory,
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $customerFactory,
        \Magenest\GiftRegistry\Model\TypeFactory $typeFactory,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->currentCustomer = $currentCustomer;
        $this->_eventFactory = $eventFactory;
        $this->_registrantFactory = $registrantFactory;
        $this->_customerFactory = $customerFactory;
        $this->_typeFactory = $typeFactory;
    }

    /**
     * @param $registryId
     * @return mixed
     */
    public function getInforEvent($registryId)
    {
        $infor = $this->_eventFactory->create()
            ->addFieldToFilter('gift_id', $registryId)
            ->addFieldToFilter('show_in_search', 'on');
        return $infor;
    }

    /**
     * @param $registryId
     * @param $type
     * @return mixed
     */
    public function getInforEventByType($registryId, $type)
    {
        $infor = $this->_eventFactory->create()
            ->addFieldToFilter('gift_id', $registryId)
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('show_in_search', 'on')
            ->getData();
        return array_pop($infor);
    }

    /**
     * Get all list result search
     *
     * @return array
     */
    public function getListRegistry()
    {
        $query = $this->getRequest()->getParams();
        $result = null;
        if (@$query['filter-selected'] == self::FILTER_BY_TITLE) {
            $title = $query['title'];
            $result = $this->searchByTitle($title, $query['type-selected']);
        } else {
            $firstName = $this->getFirstNameByRequestParams($query);
            $lastName = $this->getLastNameByRequestParams($query);
            $result = $this->searchByName($firstName, $lastName, @$query['type-selected']?@$query['type-selected']:@$query['type']);
        }
        return $result;
    }

    private function getFirstNameByRequestParams($params){
        if(key_exists('first-name',$params)){
            return $params['first-name'];
        }
        if(key_exists('event_fn',$params)){
            return $params['event_fn'];
        }
        return '';
    }

    private function getLastNameByRequestParams($params){
        if(key_exists('last-name',$params)){
            return $params['last-name'];
        }
        if(key_exists('event_ln',$params)){
            return $params['event_ln'];
        }
        return '';
    }

    public function searchByTitle($title, $type)
    {
        $title = trim($title);
        $titleResult = $this->_registrantFactory->create()
            ->getCollection();
        if ($type != "*") {
            $titleResult->getSelect()
                ->join(['registry' => $titleResult->getTable("magenest_giftregistry")], 'main_table.giftregistry_id = registry.gift_id')
                ->where("title LIKE ?", "%".$title."%")
                ->where("type = ?", $type);
            return $titleResult->getData();
        }
        $titleResult->getSelect()
            ->join(['registry' => $titleResult->getTable("magenest_giftregistry")], 'main_table.giftregistry_id = registry.gift_id')
            ->where("title LIKE ?", "%".$title."%");
        return $titleResult->getData();
    }

    public function searchByName($firstName, $lastName, $type)
    {
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $collection = $this->_registrantFactory->create()->getCollection();
        $collection->getSelect()->joinLeft(['registry' => $collection->getTable('magenest_giftregistry')],'main_table.giftregistry_id = registry.gift_id');
        $collection->addFieldToFilter('main_table.firstname', ['like' => '%' . $firstName . '%'])
            ->addFieldToFilter('main_table.lastname', ['like' => '%' . $lastName . '%']);
        if ($type != "*") {
            $collection->addFieldToFilter('registry.type',$type);
        }
        return $collection->getData();
    }

    /**
     * @return mixed
     */
    public function getTypeEvent()
    {
        return $this->getRequest()->getParam('type');
    }

    /**
     * Get firstname keyword search
     *
     * @return mixed
     */
    public function getFirstname()
    {
        $params = $this->getRequest()->getParams();

        if(key_exists('first-name',$params)){
            return $params['first-name'];
        }
        if(key_exists('event_fn',$params)){
            return $params['event_fn'];
        }
        return '';
    }

    /**
     * Get lastname keyword search
     *
     * @return mixed
     */
    public function getLastname()
    {
        $params = $this->getRequest()->getParams();

        if(key_exists('last-name',$params)){
            return $params['last-name'];
        }
        if(key_exists('event_ln',$params)){
            return $params['event_ln'];
        }
        return '';
    }

    public function getTitle()
    {
        return $this->getRequest()->getParam('title');
    }

    public function isFilterByName()
    {
        return $this->getRequest()->getParam('filter-selected') == self::FILTER_BY_NAME ? true : false;
    }

    /**
     * @param $event
     * @return string
     */
    public function getViewUrl($event)
    {
        return $this->getUrl('giftregistry').'view'.str_replace('gift', '', $event['type']).'.html?id='.$event['gift_id'];
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getSearchUrl()
    {
        return $this->getUrl('*/index/search');
    }

    public function searchTypeWedding($type)
    {
        $query = $this->getRequest()->getParams();

        $husbandName = $query['event_hn'];
        $wifeName = $query['event_wn'];

        $listEvent = $this->_eventFactory->create()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('show_in_search', 'on')
            ->addFieldToFilter('gift_options',['like' => '%"husband_name":"%'.$husbandName.'%"%'])
            ->addFieldToFilter('gift_options',['like' => '%"wife_name":"%'.$wifeName.'%"%'])
            ->getData();
        return $listEvent;
    }

    public function searchTypeBaby($type)
    {
        $babyName = $this->getBabyName();

        $listEvent = $this->_eventFactory->create()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('show_in_search', 'on')
            ->addFieldToFilter('gift_options',['like' => '%"baby_name":"%'.$babyName.'%"%'])
            ->getData();

        return $listEvent;
    }

    public function getBabyName()
    {
        return $this->getRequest()->getParam('event_bb');
    }

    public function getHusbandName()
    {
        return $this->getRequest()->getParam('event_hn');
    }

    public function getWifeName()
    {
        return $this->getRequest()->getParam('event_wn');
    }

    public function getMessageResult()
    {
        $type = $this->getTypeEvent();
        $message = '';
        $husband = $this->getHusbandName();
        $wife = $this->getWifeName();
        if ($type == 'weddinggift') {
            if ($husband != '' && $wife != '') {
                $message .= "husband's name: '{$this->getHusbandName()}' and wife's name: '{$this->getWifeName()}'";
            } else {
                if ($husband == '') {
                    $message .= "wife's name: '{$this->getWifeName()}'";
                } else {
                    $message .= "husband's name: '{$this->getHusbandName()}'";
                }
            }
        } elseif ($type == 'babygift') {
            $message .= "baby's name: '{$this->getBabyName()}'";
        } else {
            $message .= "first name: '{$this->getFirstname()}' and last name: {$this->getLastname()}'";
        }
        return $message;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * Get list type
     */
    public function getListEvent()
    {
        return $this->_typeFactory->create()->getCollection();
    }
    function stripUnicode($str){
        if(!$str) return false;
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
        );
        foreach($unicode as $nonUnicode=>$uni) $str = preg_replace("/($uni)/i",$nonUnicode,$str);
        return $str;
    }
}
