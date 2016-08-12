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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * Class PilipayPayResult
 * This class helps to deal the callback payment result.
 * Note: directly `new` operation is not supported. You should always use `PilipayPayResult::fromRequest()` to create an instance.
 *
 * For example:
 *
 *     // create an instance from the request
 *     $payResult = PilipayPayResult::fromRequest();
 *
 *     // verify whether the request is valid:
 *     if (!$payResult->verify($appSecret)){ // $appSecret is exactly the same with $order->appSecret
 *         // error handling...
 *         die('Invalid request');
 *     }
 *
 *     // judge whether payment is successfully completed:
 *     if (!$payResult->isSuccess()){
 *         // deal failure
 *     } else {
 *         // deal success
 *     }
 *
 *
 * @property $merchantNo    string  the merchant number.
 * @property $orderNo       string  the order number. It's been passed to pilibaba via PilipayOrder.
 * @property $orderAmount   number  the total amount of the order. Its unit is the currencyType in the submitted PilipayOrder.
 * @property $signType      string  "MD5"
 * @property $signMsg       string  it's used for verify the request. Please use `PilipayPayResult::verify()` to verify it.
 * @property $sendTime      string  the time when the order was sent. Its format is like "2011-12-13 14:15:16".
 * @property $dealId        string  the transaction ID in Pilibaba.
 * @property $fee           number  the fee for Pilibaba
 * @property $customerMail  string  the customer's email address.
 * @property $errorCode     string  used for recording errors. If you want to check whether the payment is successfully completed, please use `isSuccess()` instead
 * @property $errorMsg      string  used for recording errors. If you want to check whether the payment is successfully completed, please use `isSuccess()` instead
 */
class PilipayPayResult
{
    protected $_merchantNo;
    protected $_orderNo;
    protected $_orderAmount;
    protected $_signType;
    protected $_signMsg;
    protected $_fee;
    protected $_orderTime;
    protected $_customerMail;
    protected $_errorCode;
    protected $_errorMessage;
    protected $_validation;

    /**
     * @param array $request
     * @return PilipayPayResult
     */
    public static function fromRequest($request = null)
    {
        return new PilipayPayResult($request ? $request : $_REQUEST);
    }

    protected function __construct($request)
    {
        if (!empty($request)) {
            foreach ($request as $field => $value) {
                $field = '_' . $field;
                $this->{$field} = $value;
            }
        }
    }

    /**
     * @param      $appSecret
     * @param bool $throws whether throws exception when fails
     *
     * @return bool whether is valid request
     * @throws PilipayError
     */
    public function verify($appSecret, $throws = false)
    {
        $signature = $this->_merchantNo;
        $signature .= $this->_orderNo;
        $signature .= $this->_orderAmount;
        $signature .= $this->_signType;
        $signature .= $this->_fee;
        $signature .= $this->_orderTime;
        $signature .= $this->_customerMail;
        $signature .= $appSecret;
        $calcedSignMsg = md5($signature);

        if (strcasecmp($calcedSignMsg, $this->_signMsg) !== 0) {
            PilipayLogger::instance()->log("error", "Invalid signMsg: ".$this->_signMsg." with secret: ".$appSecret." with data: ".Tools::jsonEncode(get_object_vars($this)));

            if ($throws) {
                throw new PilipayError(PilipayError::INVALID_SIGN, $this->_signMsg);
            }
            $this->_validation = false;

            return false;
        }

        $this->_validation = true;

        return true;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->_validation;
    }

    /**
     * @param $name
     * @return mixed
     * @throws PilipayError
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        } else {
            throw new PilipayError(PilipayError::PROPERTY_NOT_EXIST, array($name));
        }
    }

    // setter using the default

    /**
     * return result to pilibaba
     * @param $result "1" or "OK" means result is success
     * @param $andDie bool
     * @return null
     */
    public function returnDealResultToPilibaba($result, $andDie = true)
    {
        if ($result == 1 or $result == 'OK') {
            echo 'OK';
        } else {
            echo $result;
        }

        if ($andDie) {
            die;
        }

        return null;
    }

    /**
     * @return mixed
     * @property $errorCode     string  used for recording errors. If you want to check whether the payment is successfully completed, please use `isSuccess()` instead
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    /**
     * @return mixed
     * @property $errorMsg      string  used for recording errors. If you want to check whether the payment is successfully completed, please use `isSuccess()` instead
     */
    public function getErrorMsg()
    {
        return $this->_errorMessage;
    }

    /**
     * @return mixed
     * @property $merchantNo    string  the merchant number.
     */
    public function getMerchantNO()
    {
        return $this->_merchantNo;
    }

    /**
     * @return mixed
     * @property $orderNo       string  the order number. It's been passed to pilibaba via PilipayOrder.
     */
    public function getOrderNo()
    {
        return $this->_orderNo;
    }

    /**
     * @return mixed
     * @property $orderAmount   number  the total amount of the order. Its unit is the currencyType in the submitted PilipayOrder.
     */
    public function getOrderAmount()
    {
        return $this->_orderAmount / 100; // divide it by 100 -- as it's in cents over the HTTP API.
    }

    /**
     * @return mixed
     * @property $signType      string  "MD5"
     */
    public function getSignType()
    {
        return $this->_signType;
    }

    /**
     * @return mixed
     * @property $signMsg       string  it's used for verify the request. Please use `PilipayPayResult::verify()` to verify it.
     */
    public function getSignMsg()
    {
        return $this->_signMsg;
    }

    /**
     * @return mixed
     * @property $orderTime      string  the time when the order was sent. Its format is like "2011-12-13 14:15:16".
     */
    public function getOrderTime()
    {
        return $this->_orderTime;
    }

    /**
     * @return mixed
     * @property $fee           number  the fee for Pilibaba
     */
    public function getFee()
    {
        return $this->_fee;
    }

    /**
     * @return mixed
     * @property $customerMail  string  the customer's email address.
     */
    public function getCustomerMail()
    {
        return $this->_customerMail;
    }
}
