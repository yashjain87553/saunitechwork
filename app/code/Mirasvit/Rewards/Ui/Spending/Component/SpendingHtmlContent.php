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



namespace Mirasvit\Rewards\Ui\Spending\Component;

use Magento\Ui\Component\HtmlContent;
use Magento\Ui\Component\AbstractComponent;

class SpendingHtmlContent extends HtmlContent
{
    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {

        $config = (array)$this->getData('config');
        $html = $this->block->toHtml();
        $html = preg_replace('/data-form-part="sales_rule_form"/ims',
            'data-form-part="rewards_spending_rule_form"',
            $html);
        $html = preg_replace('/data-form-part=""/ims',
            'data-form-part="rewards_spending_rule_form"',
            $html);
        $config['content'] = $html;
        $this->setData('config', $config);
        AbstractComponent::prepare();
    }
}
