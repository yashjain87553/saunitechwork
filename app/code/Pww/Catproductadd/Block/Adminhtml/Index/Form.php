<?php
namespace Pww\Catproductadd\Block\Adminhtml\Index;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template;

class Form extends Template
{
    protected $_storeManager;
     protected  $configReader;

    protected $formKey;

    /**
     * Form constructor.
     * @param Template\Context $context
     * @param array $data
     * @param FormKey $formKey
     */
    public function __construct(
        Template\Context $context,
         \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\DeploymentConfig\Reader $configReader,
        array $data = [],
        FormKey $formKey
    )
    {
         $this->configReader = $configReader;
         $this->_storeManager = $storeManager;
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
    public function getadminurl()
    {
        $config = $this->configReader->load();
        $adminSuffix = $config['backend']['frontName'];
        return $this->_storeManager->getStore()->getBaseUrl().$adminSuffix;
        
    }
}