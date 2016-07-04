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
 * Class PilipayCurrency
 * This class helps to query all  currency of pilibaba supported
 *
 */
class PilipayCurrency // extends PilipayModel
{
    const PILIPAY_CURRENCY_PATH = 'http://www.pilibaba.com/pilipay/getCurrency';

    /**
     * query all pilibaba supported currency
     * 
     * @return array
     */
    public static function queryAll()
    {
        $result = Tools::file_get_contents(self::PILIPAY_CURRENCY_PATH);
        if (empty($result)) {
            return array();
        }

        $array = Tools::jsonDecode($result, true);
        return $array;
    }

    /*
        
    */
    public static function arrayFormat()
    {
        $currencies = self::queryAll();
        $format = array();
        foreach ($currencies as $value) {
            $format[$value] = $value;
        }
        return $format;
    }
    public static function selectFormat()
    {
        $currencies = self::queryAll();
        $format = array();
        foreach ($currencies as $value) {
            $format[] = array(
                    'id'    => $value,
                    'currency'  => $value,
                );
        }
        return $format;
    }
}
