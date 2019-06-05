<?php
namespace Custom\Menu\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Helper\Category;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Indexer\Category\Flat\State;

class Mymenu
    extends Template
{
    protected $_categoryHelper;
    protected $categoryFlatConfig;

    public function __construct(
        Category $categoryHelper,
        State $categoryFlatState,
        Context $context
    ){

        $this->_categoryHelper = $categoryHelper;
        $this->categoryFlatConfig = $categoryFlatState;

        parent::__construct($context);
    }

    public function getRootCategories(){

        $categories = $this->_categoryHelper->getStoreCategories(true, false, true);

        return $categories;
    }

    public function getSubCategories($category)
    {
        if ($this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
            $subCategories = (array)$category->getChildrenNodes();
        } else {
            $subCategories = $category->getChildren();
        }
        return $subCategories;
    }

    public function getCategoryUrl($category){
        return $this->_categoryHelper->getCategoryUrl($category);
    }
}