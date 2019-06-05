<?php
namespace Pww\Topsearch\Block;
 
/*
 * Webkul Hello Block
 */
 
class Topsearch extends \Magento\Framework\View\Element\Template
{
    /**
     * @return $this
     */
    public function __construct(\Magento\Catalog\Block\Product\Context $context,\Magento\Reports\Model\ResourceModel\Product\CollectionFactory $productsFactory, array $data = []
    )
    {
        
        $this->_productsFactory = $productsFactory;
        parent::__construct($context);
    
    }
   
 
    
    public function getCollection()
    {
        $currentStoreId = $this->_storeManager->getStore()->getId();

        $collection = $this->_productsFactory->create()
        ->addAttributeToSelect(
            '*'
        )->addViewsCount()->setStoreId(
                $currentStoreId
        )->setPageSize(4)->addStoreFilter(
                $currentStoreId
        )->addAttributeToFilter(
            'status', array('eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        );
        $items = $collection->getItems();
        return $items;
    }
}