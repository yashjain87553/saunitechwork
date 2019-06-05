<?php
/**
 * @author Nir Goldman
 * @package NewsletterPopup
 */
namespace Gold\NewsletterPopup\Block;
 
class NewsletterPopup extends \Magento\Framework\View\Element\Template
{

	protected $newsletterPopupHelper;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Gold\NewsletterPopup\Helper\Data $newsletterPopupHelper,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_newsletterPopup = $newsletterPopupHelper;
	}
	
	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}
	
	
    public function getFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }


	public function  getConfigVars($section,$group,$field)
	{
		return $this->_newsletterPopup->getConfigVars($section,$group,$field);
	}

}