<?php
/**
 * Created by Magenest.
 * User: trongpq
 * Date: 3/2/18
 * Time: 10:26
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Ui\Component\Listing\Column\Order;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

/**
 * Class ViewGift
 * @package Magenest\GiftRegistry\Ui\Component\Listing\Column\Order
 */
class ViewGift extends Column
{
    /**
     * @var UrlBuilder
     */
    protected $actionUrlBuilder;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $registrantFactory;
    /**
     * @var string
     */
    private $_editUrl = 'giftregistrys/registry/edit';

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        \Magenest\GiftRegistry\Model\RegistrantFactory $registrantFactory,
        array $components = [],
        array $data = []
    ) {

        $this->urlBuilder = $urlBuilder;
        $this->registrantFactory = $registrantFactory;
        $this->actionUrlBuilder = $actionUrlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['giftregistry_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->_editUrl, ['registrant_id' => $this->getRegistrantId($item['giftregistry_id'])]),
                        'label' => __('View Registry'),
                        'hidden' => false,
                    ];
                }
            }
        }
        return $dataSource;
    }

    private function getRegistrantId($giftregistryId)
    {
        $registrantId = "0";
        $registrant = $this->registrantFactory->create();
        $registrantData = $registrant->getCollection()->addFieldToFilter('giftregistry_id', $giftregistryId)->getFirstItem()->getData();
        return @$registrantData['registrant_id'];
    }
}
