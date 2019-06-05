<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 01/12/2015
 * Time: 13:25
 */

namespace Magenest\GiftRegistry\Controller\Customer;

use Braintree\Exception;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as MagentoDateTime;
use Magento\TestFramework\Event\Magento;

/**
 * Class Post
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class Post extends Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var GiftRegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\RegistrantFactory
     */
    protected $registrantFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $_context;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $_currentCustomer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var MagentoDateTime
     */
    protected $date;

    protected $data;

    protected $timezone;

    /**
     * Post constructor.
     * @param GiftRegistryFactory $registryFactory
     * @param \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory
     * @param \Magenest\GiftRegistry\Model\AddressFactory $addressFactory
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param MagentoDateTime $date
     */
    public function __construct(
        GiftRegistryFactory $registryFactory,
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        \Magenest\GiftRegistry\Model\AddressFactory $addressFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        MagentoDateTime $date,
        \Magenest\GiftRegistry\Helper\Data $data,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
    
        $this->date = $date;
        $this->_context = $context;
        $this->_currentCustomer = $currentCustomer;
        $this->_customerSession = $session;
        $this->registryFactory = $registryFactory;
        $this->registrantFactory = $registrantFactory;
        $this->addressFactory = $addressFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->data =$data;
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getGiftRegistryId()
    {
        return $this->getParam('event_id') ? $this->getParam('event_id') : null;
    }

    /**
     * @return mixed
     */
    public function getEventType()
    {
        return $this->getParam('type');
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    public function getParam($param)
    {
        return $this->getRequest()->getParam($param);
    }

    /** Get option of Baby */
    public function getBabyGiftOption()
    {
        $params = $this->getRequest()->getParams();
        $options = array();
        $options['baby_name'] = $params['baby_name'];
        return json_encode($options,JSON_UNESCAPED_UNICODE);
    }

    /** Get option of birthday */
    public function getBirthdayGiftOption()
    {
        $params = $this->getRequest()->getParams();
        $options = array();
        $options['date-birthday'] = $params['date-birthday'];
        $options['name-birthday'] = $params['name-birthday'];
        return json_encode($options,JSON_UNESCAPED_UNICODE);
    }

    /** Get option of Wedding */
    public function getWeddingGiftOption()
    {
        $params = $this->getRequest()->getParams();
        $options = array();
        $options['husband_name'] = $params['husband_name'];
        $options['wife_name'] = $params['wife_name'];
        return json_encode($options,JSON_UNESCAPED_UNICODE);
    }

    /** Get option of Christmas */
    public function getChristmasGiftOption()
    {
        $params = $this->getRequest()->getParams();
        $options = array();
        $options['greeting'] = $params['greeting'];
        return json_encode($options,JSON_UNESCAPED_UNICODE);
    }

    /** Add more type of event here */
    public function getEventGiftOption()
    {
        /** return option */
        return;
    }

    public function save()
    {
        $data = $this->getRequest()->getParams();

        /** Get event option */
        switch ($this->getEventType()) {
            case 'babygift':
                $data['gift_options'] = $this->getBabyGiftOption();
                break;
            case 'birthdaygift':
                $data['gift_options'] = $this->getBirthdayGiftOption();
                break;
            case 'weddinggift':
                $data['gift_options'] = $this->getWeddingGiftOption();
                break;
            case 'christmasgift':
                $data['gift_options'] = $this->getChristmasGiftOption();
                break;
        }
        if ($data) {
            /** Validate Param */

            //remove whitespace in password
            $data['password'] = str_replace(' ', '', $data['password']);
            $data['re_password'] = str_replace(' ', '', $data['re_password']);

            $data['title'] = htmlspecialchars($this->getParam('title'), ENT_QUOTES, 'UTF-8');
            $data['description'] = htmlspecialchars($this->getParam('description'), ENT_QUOTES, 'UTF-8');
            $dateOfRegistry = $data['date'];
            $date = DateTime::createFromFormat('m-d-Y', $dateOfRegistry);
            $data['date'] = ($date->format('Y-m-d H:i:s'));
            $data['date'] = $this->getDateTimezone($data['date']);
            $data['updated_at'] = $this->date->gmtDate('Y-m-d H:i:s');
            $data['show_in_search'] = isset($data['show_in_search']) ? $data['show_in_search'] : '';
        }

        $giftRegistryId = $this->getGiftRegistryId();

        $registrantPostDatas = $this->getRequest()->getParam('registrant');

        try {
            // validate birthday date
            if($this->getEventType() == 'birthdaygift'){
                if(!$this->validateBirthDay($data['date-birthday'])){
                    $this->messageManager->addError(__('Invalid Birthday Date.'));
                    return;
                }
            }
            if ($giftRegistryId) {
                /** Update */
                if ($data['password'] != $data['re_password']) {
                    $this->messageManager->addError(__('Password and re-password does not match.'));
                    return;
                }

                unset($data['re_password']);
                if($data['password']){
                    $data['password'] = md5($data['password']);
                } else{
                    unset($data['password']);
                }

                unset($data['gift_id']);
                $model = $this->registryFactory->create()->load($giftRegistryId);
                $model->addData($data);

                $password = $model->getPassword();
                if($data['privacy'] == 'private' && $password == md5('')){
                    $this->messageManager->addError(__('You haven\'t specified password in private mode.'));
                    return;
                }

                $model->save();

                /** Update Registrant Information */
                if (isset($registrantPostDatas) && !empty($registrantPostDatas)) {
                    foreach ($registrantPostDatas as $key => $value) {
                        $registrant = $this->registrantFactory->create()->load($key)->addData($value)->save();
                    }
                }
                $this->messageManager->addSuccess('The Registry has been updated!');
            } else {
                /** Create new Registry */
                if ($data['password'] != $data['re_password']) {
                    $this->messageManager->addError(__('Password and re-password does not match.'));
                    return;
                }
                if($data['privacy'] == 'private' && $data['password'] == ''){
                    $this->messageManager->addError(__('Please enter password in private mode.'));
                    return;
                }

                unset($data['re_password']);
                $data['password'] = md5($data['password']);
                unset($data['gift_id']);
                $data['customer_id'] = $this->getCustomerId();
                $model = $this->registryFactory->create()->setData($data);
                $model->setData('is_expired',0);
                $model->save();
                $giftRegistryId = $model->getId();

                if (isset($registrantPostDatas) && !empty($registrantPostDatas)) {
                    foreach ($registrantPostDatas as $key => $value) {
                        if ($giftRegistryId > 0) {
                            $value['giftregistry_id'] = $giftRegistryId;
                            $registrant = $this->registrantFactory->create()->setData($value)->save();
                        }
                    }
                }
                $this->messageManager->addSuccess('The Registry has been created successfully!');
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('There is error while saving the gift registry.'));
        }
        return $giftRegistryId;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|void
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->_customerSession->isLoggedIn()) {
            $this->messageManager->addWarning(__('Please login to continue.'));
            return;
        }
        // validate when create new event
        if(!isset($data['event_id'])){
            if($this->validateIsHaveUnexpired()){
                $this->messageManager->addWarning(__('You can create one unexpired gift each type only.'));
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }
            // validate birthday date
            if(isset($data['date-birthday'])){
                if(!$this->validateBirthDay($data['date-birthday'])){
                    $this->messageManager->addError(__('Invalid Birthday Date.'));
                    $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                    return $resultRedirect;
                }
            }
        }
        /** Save GiftRegistry's Information */
        $this->save();

        /**
         * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
         */
        $resultRedirect->setPath('giftregistry/'.'manage'.str_replace('gift', '', $this->getEventType()).'.html');
        if($this->getGiftRegistryId() && $this->getEventType()){
            $resultRedirect->setPath('giftregistrys/index/manageregistry/type/'. $this->getEventType().'/event_id/'.$this->getGiftRegistryId());
        }
        return $resultRedirect;
    }

    private function validateIsHaveUnexpired()
    {
        $newGiftDate = $this->getRequest()->getParam('date');
        $type = $this->getRequest()->getParam('type');
        if($newGiftDate){
            if($this->data->isHaveUnexpiredGiftByDate($this->_customerSession->getCustomerId(),$type)){
                return true;
            }
        }
        return false;
    }

    private function validateDate($date, $format = 'm-d-Y')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

    private function getDateTimezone($date)
    {
        $date = $this->timezone->date(new \DateTime($date));
        return $date->format('Y-m-d');
    }

    private function validateBirthDay($date,  $format = 'm-d-Y')
    {
        $d = DateTime::createFromFormat($format, $date);
        if($d && $d->format($format) === $date){
            $dateCurrent = date_create();
            if($d <= $dateCurrent){
                return true;
            }
        }
        return false;
    }
}
