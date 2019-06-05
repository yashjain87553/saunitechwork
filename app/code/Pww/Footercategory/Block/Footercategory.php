<?php

namespace Pww\Footercategory\Block;;

class Footercategory extends \Magento\Framework\View\Element\Template
{
    
    protected $_category;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Category $category,
        array $data = []
    ) {
        $this->_category=$category;
        parent::__construct($context, $data);
    }

    public function loadcat($categoryId)
    {
        return $this->_category->load($categoryId);
    }
}