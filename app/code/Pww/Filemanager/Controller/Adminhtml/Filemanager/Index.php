<?php

namespace Pww\Filemanager\Controller\Adminhtml\Filemanager;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Page result factory
     * 
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Page factory
     * 
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * constructor
     * 
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * execute the action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->setPageData();
        return $this->getResultPage();
    }
    /**
     * instantiate result page object
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page
     */
    public function getResultPage()
    {
        if (is_null($this->resultPage)) {
            $this->resultPage = $this->resultPageFactory->create();
        }
        return $this->resultPage;
    }
    /**
     * set page data
     *
     * @return $this
     */
    protected function setPageData()
    {
        $resultPage = $this->getResultPage();
        //$resultPage->setActiveMenu('Mageplaza_BetterSlider::slider');
        $resultPage->getConfig()->getTitle()->prepend((__('Filemanager')));
        return $this;
    }
}
