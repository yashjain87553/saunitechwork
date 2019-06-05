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



namespace Mirasvit\Rewards\Model\Order\Pdf\Total;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;

class Rewards extends DefaultTotal
{
    public function __construct(
        \Mirasvit\Rewards\Helper\Purchase $purchaseHelper,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        array $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);

        $this->purchaseHelper = $purchaseHelper;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        $amount = 0;

        $purchase = $this->purchaseHelper->getByOrder($this->getOrder());
        if ($purchase && $purchase->getSpendAmount() > 0) {
            $amount = $purchase->getSpendAmount();
        }

        return $amount;
    }
}
