<?php
/**
 * Created by Magenest.
 * User: trongpq
 * Date: 4/23/18
 * Time: 08:02
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
    }

    /**
     * Validate and Match
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $pathInfo = $request->getPathInfo();
        $identifier = trim($pathInfo, '/');
        if (strpos($identifier, 'giftregistry/managewedding.html') !== false) {
            //Manage WeddingGift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('manageregistry')->setParam('type', 'weddinggift');
        } elseif (strpos($identifier, 'giftregistry/managebaby.html') !== false) {
            //Manage BabyGift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('manageregistry')->setParam('type', 'babygift');
        } elseif (strpos($identifier, 'giftregistry/managebirthday.html') !== false) {
            //Manage BirthdayGift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('manageregistry')->setParam('type', 'birthdaygift');
        } elseif (strpos($identifier, 'giftregistry/managechristmas.html') !== false) {
            //Manage ChristmasGift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('manageregistry')->setParam('type', 'christmasgift');
        } elseif (strpos($identifier, 'giftregistry.html') !== false) {
            //List Gift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('listgift');
        } elseif (strpos($identifier, 'giftregistry/wedding.html') !== false) {
            //Show WeddingGift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('showgift')->setParam('type', 'weddinggift');
        } elseif (strpos($identifier, 'giftregistry/baby.html') !== false) {
            //Show BabyGift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('showgift')->setParam('type', 'babygift');
        } elseif (strpos($identifier, 'giftregistry/birthday.html') !== false) {
            //Show BirthdayGift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('showgift')->setParam('type', 'birthdaygift');
        } elseif (strpos($identifier, 'giftregistry/christmas.html') !== false) {
            //Show ChristmasGift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('showgift')->setParam('type', 'christmasgift');
        } elseif (strpos($identifier, 'giftregistry/newwedding.html') !== false) {
            //New WeddingGift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('newgift')->setParam('type', 'weddinggift');
        } elseif (strpos($identifier, 'giftregistry/newbaby.html') !== false) {
            //New BabyGift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('newgift')->setParam('type', 'babygift');
        } elseif (strpos($identifier, 'giftregistry/newbirthday.html') !== false) {
            //New BirthdayGift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('newgift')->setParam('type', 'birthdaygift');
        } elseif (strpos($identifier, 'giftregistry/newchristmas.html') !== false) {
            //New ChristmasGift
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('newgift')->setParam('type', 'christmasgift');
        } elseif (strpos($identifier, 'giftregistry/viewwedding.html') !== false) {
            //View WeddingGift
            $request->setModuleName('giftregistrys')->setControllerName('guest')->setActionName('view')->setParam('type', 'weddinggift');
        } elseif (strpos($identifier, 'giftregistry/viewbaby.html') !== false) {
            //View BabyGift
            $request->setModuleName('giftregistrys')->setControllerName('guest')->setActionName('view')->setParam('type', 'babygift');
        } elseif (strpos($identifier, 'giftregistry/viewbirthday.html') !== false) {
            //View Birthday
            $request->setModuleName('giftregistrys')->setControllerName('guest')->setActionName('view')->setParam('type', 'birthdaygift');
        } elseif (strpos($identifier, 'giftregistry/viewchristmas.html') !== false) {
            //View Christmas
            $request->setModuleName('giftregistrys')->setControllerName('guest')->setActionName('view')->setParam('type', 'christmasgift');
        } elseif (strpos($identifier, 'giftregistry/search.html') !== false) {
            //List Search
            $request->setModuleName('giftregistrys')->setControllerName('index')->setActionName('listsearch');
        } elseif (strpos($identifier, 'giftregistry/searchtype.html') !== false) {
            //Type Search
            $request->setModuleName('giftregistrys')->setControllerName('guest')->setActionName('searchtypegift');
        } elseif (strpos($identifier, 'customer/giftregistry') !== false) {
            //My Gift Registry
            $request->setModuleName('giftregistrys')->setControllerName('customer')->setActionName('mygiftregistry');
        } else {
            return null;
        }
        return $this->actionFactory->create('Magento\Framework\App\Action\Forward', ['request' => $request]);
    }
}
