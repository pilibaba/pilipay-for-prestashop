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
 * @property $version      string  API version. currently v1.0.1
 * @property $merchantNO   string  merchant number in account info page after signed up in pilibaba.com
 * @property $appSecret    string  app secret key in account info page
 * @property $currencyType string  USD/EUR/GBP/AUD/CAD/JPY...
 * @property $orderNo      string  order number in your site, which identifies an order
 * @property $orderAmount  number  total order amount in currencyType
 * @property $orderTime    string  the time when the order was created, in format of 2001-12-13 14:15:16
 * @property $sendTime     string  the time when the order was sent, in format of 2001-12-13 14:15:16
 * @property $pageUrl      string  the order's checkout page
 * @property $serverUrl    string  the return URL after payment is completed successfully
 * @property $shipper      number  ship fee (it's to houseware's fee, not the international ship fee) (in currencyType)
 * @property $tax          number  sales tax (in currencyType)
 *
 * @property $signType     string  "MD5" (fixed)
 * @property $signMsg      string  = MD5(merchantNO+orderNo+orderAmount+sendTime+appSecrect) (auto calculated)
 *
 * goods -- should use addGood() to add goods to the order
 *
 */
class PilipayOrder extends PilipayModel
{

    private $_goodsList = array();

    public function __construct($properties = array())
    {
        $this->version  = '1.0.1';
        $this->signType = 'MD5';

        parent::__construct($properties);
    }

    /**
     * @return array order data in API form
     * @throws PilipayError
     */
    public function toApiArray()
    {
        // sign
        if ($this->signType == 'MD5') {
            // sign using MD5
            // not: orderAmount should be in cents
            $this->signMsg = md5(
              $this->merchantNO.$this->orderNo.(int)round(
                $this->orderAmount * 100
              ).$this->sendTime.$this->appSecret
            );
        } else {
            throw new PilipayError(
              PilipayError::INVALID_ARGUMENT,
              array('name' => 'signType', 'value' => $this->signType)
            );
        }

        // check goods list
        if (empty($this->_goodsList)) {
            throw new PilipayError(
              PilipayError::REQUIRED_ARGUMENT_NO_EXIST,
              array(
                'name'  => 'goodsList',
                'value' => Tools::jsonEncode($this->_goodsList),
              )
            );
        }

        // verify
        parent::verifyFields();

        return array_map(
          'strval',
          array(
            'version'      => $this->version,
            'merchantNO'   => $this->merchantNO,
            'currencyType' => $this->currencyType,
            'orderNo'      => $this->orderNo,
            'orderAmount'  => (int)round($this->orderAmount * 100),
              // API: need to be in cent
            'orderTime'    => $this->orderTime,
            'sendTime'     => $this->sendTime,
            'pageUrl'      => $this->pageUrl,
            'serverUrl'    => $this->serverUrl,
            'shipper'      => (int)round($this->shipper * 100),
              // API: need to be in cent
            'tax'          => (int)round($this->tax * 100),
              // API: need to be in cent
            'signType'     => $this->signType,
            'signMsg'      => $this->signMsg,
            'goodsList'    => urlencode(Tools::jsonEncode($this->_goodsList)),
          )
        );
    }

    /**
     * 提交订单
     * @return array
     * @throws PilipayError
     */
    public function submit()
    {
        $orderData = $this->toApiArray();

        PilipayLogger::instance()
                     ->log(
                       'info',
                       'Submit order begin: '.Tools::jsonEncode($orderData)
                     );

        // submit
        $curl = new PilipayCurl();
        $curl->post(PilipayConfig::getSubmitOrderUrl(), $orderData);
        $responseStatusCode = $curl->getResponseStatusCode();
        $nextUrl            = $curl->getResponseRedirectUrl();

        PilipayLogger::instance()
                     ->log(
                       'info',
                       'Submit order end: '.print_r(
                         array(
                           'request'  => $orderData,
                           'response' => array(
                             'statusCode' => $curl->getResponseStatusCode(),
                             'statusText' => $curl->getResponseStatusText(),
                             'nextUrl'    => $nextUrl,
                             'content'    => $curl->getResponseContent(),
                           ),
                         ),
                         true
                       )
                     );

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

        $orderData = $this->toApiArray();

        PilipayLogger::instance()
                     ->log(
                       'info',
                       "Submit order (using {$method} form): ".Tools::jsonEncode(
                         $orderData
                       )
                     );

        $fields = '';
        foreach ($orderData as $name => $value) {
            $input = $this->context->smarty->fetch(
              realpath(
                dirname(__FILE__).'/..'
              ).'/views/templates/admin/input.tpl'
            );
            $fields .= sprintf($input, $name, htmlspecialchars($value));
        }
        $this->context->smarty->assign('action', $action);
        $this->context->smarty->assign('method', $method);
        $this->context->smarty->assign('fields', $fields);
        $html = $this->context->smarty->fetch(
          realpath(dirname(__FILE__).'/..').'/views/templates/admin/submit.tpl'
        );

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
        );

        PilipayLogger::instance()
                     ->log(
                       'info',
                       "Update track NO: ".Tools::jsonEncode($params)
                     );

        $curl = new PilipayCurl();
        $curl->get(PilipayConfig::getUpdateTrackNoUrl(), $params);

        PilipayLogger::instance()
                     ->log(
                       'info',
                       'Update track NO result: '.print_r(
                         array(
                           'request'  => $params,
                           'response' => array(
                             'statusCode' => $curl->getResponseStatusCode(),
                             'statusText' => $curl->getResponseStatusText(),
                             'content'    => $curl->getResponseContent(),
                           ),
                         ),
                         true
                       )
                     );
    }

    /**
     * 添加商品信息
     * Add goods info
     *
     * @param PilipayGood $good 商品信息
     */
    public function addGood(PilipayGood $good)
    {
        $this->_goodsList[] = $good->toApiArray();
    }

    /**
     * Get the barcode's Picture URL
     * -- this barcode should be print on the cover of package before shipping, so that our warehouse could easily match the package.
     * 获取条形码的图片URL
     * -- 在邮寄前, 这个条形码应该打印到包裹的包装上, 以便我们的中转仓库识别包裹.
     * @return string the barcode's Picture URL
     */
    public function getBarcodePicUrl()
    {
        return PilipayConfig::getBarcodeUrl().'?'.http_build_query(
          array(
            'merchantNo' => pSQL($this->merchantNO),
            'orderNo'    => pSQL($this->orderNo),
          )
        );
    }

    public function getNumericFieldNames()
    {
        return array('orderAmount', 'shipper', 'tax');
    }

    public function getRequiredFieldNames()
    {
        return array(
          'version',
          'merchantNO',
          'appSecret',
          'currencyType',
          'orderNo',
          'orderAmount',
          'orderTime',
          'sendTime',
          'pageUrl',
          'serverUrl',
          'shipper',
          'tax',
          'signType',
          'signMsg',
        );
    }
}
