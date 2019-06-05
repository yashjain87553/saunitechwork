<?php
namespace Pww\Filemanager\Controller\Adminhtml\Filemanager;
 
use Magento\Framework\App\Filesystem\DirectoryList;
 
class Download extends \Magento\Backend\App\Action
{
    protected $orderRepository;
    protected $_fileFactory;
    protected $directory;
    protected $eavAttributeFactory;
    protected $attributeOptionManagement;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $eavAttributeFactory,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
         $this->eavAttributeFactory = $eavAttributeFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $data = $this->getRequest()->getPost();
                $magentoAttribute = $this->eavAttributeFactory->create()->loadByCode('catalog_product', $data['file_type']);
                $attributeCode = $magentoAttribute->getAttributeCode();
                $magentoAttributeOptions = $this->attributeOptionManagement->getItems(
                    'catalog_product',
                    $attributeCode
                );
                $name = date('m_d_Y_H_i_s');
                $filepath = 'export/custom' . $name . '.csv';
                $this->directory->create('export');
                $stream = $this->directory->openFile($filepath, 'w+');
                $stream->lock();
                foreach($magentoAttributeOptions as $option) {
                    if (!$option->getValue()) {
                        continue;
                    }
                    if($option->getLabel() instanceof \Magento\Framework\Phrase) {
                        $label = $option->getText();
                    } else {
                        $label = $option->getLabel();
                    }

                    if($label == '') {
                        continue;
                    }
                     
                        $itemData = [];
            $itemData[] = $label;
            $stream->writeCsv($itemData);

                }

                 $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder
 
        $csvfilename = $data['file_type'].'Attribute_Data.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
       
        
    }
}