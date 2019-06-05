<?php
namespace Pww\Catproductadd\Controller\Adminhtml\Catproductadd;
 
use Magento\Framework\App\Filesystem\DirectoryList;
 
class Download extends \Magento\Backend\App\Action
{
    protected $orderRepository;
    protected $_fileFactory;
    protected $directory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }
 
    public function execute()
    {
        $name = date('m_d_Y_H_i_s');
        $filepath = 'export/custom' . $name . '.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $sku = array("I0011489","I0009813","I0009814","I0009815","I0009816","I0009817","I0009818");
        foreach ($sku as $item) {
            $itemData = [];
            $itemData[] = $item;
            $stream->writeCsv($itemData);
        }
        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder
 
        $csvfilename = 'category_product_add_sample.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        
    }
}