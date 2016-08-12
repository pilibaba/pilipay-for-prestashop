<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Class PilipayOrder
 *
 * required:
 * @property $version      string  API version.
 * @property $merchantNO   string  merchant number in account info page after signed up in pilibaba.com
 * @property $currencyType string  USD/EUR/GBP/AUD/CAD/JPY...
 * @property $orderNo      string  order number in your site, which identifies an order
 * @property $orderAmount  number  total order amount in currencyType
 * @property $orderTime    string  the time when the order was created, in format of 2001-12-13 14:15:16
 * @property $pageUrl      string  the order's checkout page
 * @property $serverUrl    string  the return URL after payment is completed successfully
 * @property $redirectUrl  string  pay success return page to user
 * @property $notifyType   string  what type of code I return. The value: html, json.
 * @property $shipper      number  ship fee (it's to houseware's fee, not the international ship fee) (in currencyType)
 * @property $tax          number  sales tax (in currencyType)
 *
 * @property $signType     string  "MD5" (fixed)
 * @property $signMsg      string  the sign messaged. it will be autometically calcuated
 *
 * @property $appSecret    string  app secret key in account info page
 *
 * as to goods -- you should use addGood() to add goods to the order
 *
 */
class PilipayOrder extends PilipayModel
{

    private $_goodsList = array();

    public function __construct($properties = array())
    {
        $this->version    = 'V2.0.01';
        $this->signType   = 'MD5';
        $this->notifyType = 'html';

        parent::__construct($properties);
    }

    /**
     * @return array order data in API form
     * @throws PilipayError
     */
    public function toApiArray()
    {

        // check goods list
        if (empty($this->_goodsList)) {
            throw new PilipayError(PilipayError::REQUIRED_ARGUMENT_NO_EXIST, array('name'  => 'goodsList',
                                                                                   'value' => json_encode($this->_goodsList),
            ));
        }

        // if the orderTime or sendTime is omitted, use current time
        if (empty($this->orderTime)) {
            $now             = date_create('now', timezone_open('Asia/Shanghai'))->format('Y-m-d H:i:s');
            $this->orderTime = $this->orderTime ? $this->orderTime : $now;
        }

        // verify
        parent::verifyFields();

        $apiArray = array_map('strval', array(
            'version'      => $this->version,
            'merchantNo'   => $this->merchantNO,
            'currencyType' => $this->currencyType,
            'orderNo'      => $this->orderNo,
            'orderAmount'  => (int)round($this->orderAmount * 100),
            'orderTime'    => $this->orderTime,
            'pageUrl'      => $this->pageUrl,
            'serverUrl'    => $this->serverUrl,
            'redirectUrl'  => $this->redirectUrl,
            'notifyType'   => $this->notifyType,
            'shipper'      => (int)round($this->shipper * 100),
            'tax'          => (int)round($this->tax * 100),
            'signType'     => $this->signType,
        ));
        // sign
        if ($this->signType == 'MD5') {
            // sign using MD5
            $this->signMsg       = md5(implode('', $apiArray).$this->appSecret);
            $apiArray['signMsg'] = $this->signMsg;
        } else {
            throw new PilipayError(PilipayError::INVALID_ARGUMENT, array(
                'name'  => 'signType',
                'value' => $this->signType,
            ));
        }
//        echo '<pre>';
//        print_r($this->_goodsList);
//        echo '</pre>';
//        exit;
        $apiArray['goodsList'] = urlencode(Tools::jsonEncode($this->_goodsList));
        return $apiArray;
    }

    /**
     * 提交订单
     * @return array
     * @throws PilipayError
     */
    public function submit()
    {
        $this->notifyType = 'html';
        $orderData        = $this->toApiArray();

        PilipayLogger::instance()->log('info', 'Submit order begin: '.Tools::jsonEncode($orderData));

        // submit
        $curl = new PilipayCurl();
        $curl->post(PilipayConfig::getSubmitOrderUrl(), $orderData);
        $responseStatusCode = $curl->getResponseStatusCode();
        $nextUrl            = $curl->getResponseRedirectUrl();

        PilipayLogger::instance()->log('info', 'Submit order end: '.print_r(array(
                'url'      => PilipayConfig::getSubmitOrderUrl(),
                'request'  => $orderData,
                'response' => array(
                    'statusCode' => $curl->getResponseStatusCode(),
                    'statusText' => $curl->getResponseStatusText(),
                    'nextUrl'    => $nextUrl,
                    'content'    => $curl->getResponseContent(),
                ),
            ), true));

        return array(
            'success'   => $responseStatusCode < 400 && !empty($nextUrl),
            'errorCode' => $responseStatusCode,
            'message'   => $curl->getResponseContent(),
            'nextUrl'   => $nextUrl,
        );
    }

    /**
     * @param string $method
     *
     * @return string
     */
    public function renderSubmitForm($method = "POST")
    {
        $this->context = Context::getContext();
        $action        = PilipayConfig::getSubmitOrderUrl();

        $this->notifyType = 'html';
        $orderData        = $this->toApiArray();

        PilipayLogger::instance()->log('info', "Submit order (using {$method} form): ".Tools::jsonEncode($orderData));

        $this->context->smarty->assign('orderData', $orderData);
        $this->context->smarty->assign('action', $action);
        $this->context->smarty->assign('method', $method);
        $html = $this->context->smarty->fetch(realpath(dirname(__FILE__).'/..').'/views/templates/admin/submit.tpl');

        return $html;
    }

    /**
     * Update track number (logistics number)
     *
     * @param $logisticsNo
     */
    public function updateTrackNo($logisticsNo)
    {
        $params = array(
            'orderNo'     => pSQL($this->orderNo),
            'merchantNo'  => pSQL($this->merchantNO),
            'logisticsNo' => pSQL($logisticsNo),
            'signMsg'     => md5($this->orderNo.$logisticsNo.$this->merchantNo.$this->appSecret),
        );

        PilipayLogger::instance()->log('info', "Update track NO: ".Tools::jsonEncode($params));

        $curl = new PilipayCurl();
        $curl->get(PilipayConfig::getUpdateTrackNoUrl(), $params);

        PilipayLogger::instance()->log('info', 'Update track NO result: '.print_r(array(
                'request'  => $params,
                'response' => array(
                    'statusCode' => $curl->getResponseStatusCode(),
                    'statusText' => $curl->getResponseStatusText(),
                    'content'    => $curl->getResponseContent(),
                ),
            ), true));
    }

    /**
     * 添加商品信息
     * Add goods info
     * @param PilipayGood $good 商品信息
     */
    public function addGood(PilipayGood $good)
    {
        $this->_goodsList[] = $good->toApiArray();
    }
    
    public function getBarcodePicUrl()
    {
        return PilipayConfig::getBarcodeUrl().'?'.http_build_query(array(
                'merchantNo' => pSQL($this->merchantNO),
                'orderNo'    => pSQL($this->orderNo),
            ));
    }

    public function getNumericFieldNames()
    {
        return array('orderAmount', 'shipper', 'tax');
    }

    public function getRequiredFieldNames()
    {
        return array(
            'version',
            'merchantNo',
            'appSecret',
            'currencyType',
            'orderNo',
            'orderAmount',
            'orderTime',
            'pageUrl',
            'serverUrl',
            'redirectUrl',
            'notifyType',
            'shipper',
            'tax',
            'signType',
        );
    }
}
