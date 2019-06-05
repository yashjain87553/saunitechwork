<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 25/12/2015
 * Time: 15:00
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Registry;

/**
 * Class Edit
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Registry
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_logger = $logger;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_GiftRegistry::registry')
            ->addBreadcrumb(__('Manage Event Registry'), __('Manage Event Registry'));
        return $resultPage;
    }

    /**
     * Edit Mapping
     *
     * @return                                  \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $params = $this->getRequest()->getParams();

        // 1.2 check registrant id exist
        if(!@$params['registrant_id']){
            $this->messageManager->addError(__('This registry no longer exists.'));
            /**
             * \Magento\Backend\Model\View\Result\Redirect $resultRedirect
             */
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $model = $this->_objectManager->create('Magenest\GiftRegistry\Model\Registrant')->load($params['registrant_id']);

        $modelRegistry = $this->_objectManager->create('Magenest\GiftRegistry\Model\GiftRegistry')->load($model->getGiftregistryId());


        // 2. Initial checking
        if ($params['registrant_id']) {
            $model->load($params['registrant_id']);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This registry no longer exists.'));
                /**
                 * \Magento\Backend\Model\View\Result\Redirect $resultRedirect
                 */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('registry', $model);
        $this->_coreRegistry->register('information', $modelRegistry);

        // 5. Build edit form
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __('Manage Gift Registry') : __('Manage Gift Registry'));

        return $resultPage;
    }
}
