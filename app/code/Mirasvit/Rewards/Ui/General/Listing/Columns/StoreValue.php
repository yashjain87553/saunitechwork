<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   2.3.12
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rewards\Ui\General\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Mirasvit\Rewards\Helper\Storeview;

class StoreValue extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function __construct(
        Storeview $helpdeskStoreview,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->helpdeskStoreview = $helpdeskStoreview;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $config = $this->getConfiguration();
        if (!isset($config['columnName'])) {
            return $dataSource;
        }
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if ($this->getData('name') == $config['columnName']) {
                    $object = new \Magento\Framework\DataObject($item);
                    $item[$this->getData('name')] = $this->helpdeskStoreview
                        ->getStoreViewValue($object, $this->getData('name'));
                }
            }
        }

        return $dataSource;
    }
}
