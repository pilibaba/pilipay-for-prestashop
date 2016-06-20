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
 * Class PilipayError
 * This class represents for errors in Pilipay.
 * For reducing the library's size, we use error code to distinguish different types of errors.
 */
class PilipayError extends Exception
{
    const INVALID_ARGUMENT = 411;
    const REQUIRED_ARGUMENT_NO_EXIST = 412;
    const INVALID_SIGN = 413;
    const PROPERTY_NOT_EXIST = 414;
    const INVALID_CURL_PARAMS_FORMAT = 511;

    /**
     * @param int $errorCode
     * @param array|string $errorData
     * @param Exception|null $previous
     */
    public function __construct($errorCode, $errorData, $previous = null)
    {
        $msg = $this->buildErrorMessage($errorCode, $errorData);
        parent::__construct($msg, $errorCode, $previous);
    }

    /**
     * @param int $errorCode
     * @param array|string $errorData
     * @return string
     */
    protected function buildErrorMessage($errorCode, $errorData)
    {
        if (is_array($errorData)) {
            $params = array();
            foreach ($errorData as $key => $val) {
                $params['{' . $key .'}'] = $val;
            }
        } else {
            $params = array('{}' => $errorData, '{0}' => $errorData);
        }

        return strtr(self::$errorCodeToMessageMap[$errorCode], $params);
    }

    protected static $errorCodeToMessageMap = array(
        self::INVALID_ARGUMENT => 'Invalid {name}: {value}',
        self::REQUIRED_ARGUMENT_NO_EXIST => 'The required {name} is empty: {value}',
        self::INVALID_SIGN => 'Invalid sign: {}',
        self::PROPERTY_NOT_EXIST => 'Property not exist: {}',
        self::INVALID_CURL_PARAMS_FORMAT => 'Invalid CURL params\' format: {}',
    );
}
