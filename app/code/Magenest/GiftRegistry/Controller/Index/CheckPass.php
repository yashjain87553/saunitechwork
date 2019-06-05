<?php
/**
 * Created by Magenest.
 * User: trongpq
 * Date: 4/23/18
 * Time: 12:38
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class CheckPass extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context
    ) {
        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getPostValue();
        $information = [];
        $pass = md5($params['pass1']);
        if ($pass == $params['pass2']) {
            $information['check'] = true;
        } else {
            $information['check'] = false;
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($information);
        return $resultJson;
    }
}
