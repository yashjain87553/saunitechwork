<?php

namespace Gold\NewsletterPopup\Helper;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{


	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context
	) {
		parent::__construct($context);
	}



	public function getConfigVars($section,$group,$field)
	{


		$config_path=$section.'/'.$group.'/'.$field;
		return $this->scopeConfig->getValue(
			$config_path,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}
}