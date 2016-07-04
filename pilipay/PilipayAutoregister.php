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
class PilipayAutoregister // extends PilipayModel
{
    const PILIPAY_AUTOREGISTER_URL = 'http://en.pilibaba.com/autoRegist';

    const PLATFORM_NO = '0210000489'; //'0210000451'; // x-cart.com platformNo
    const SECRECT_KEY = 'u8r0rpgj';//'cuej80z6'; // x-cart.com secrect-key

    /**
     * query all pilibaba supported currency
     * 
     * @return array
     */
    public static function register($array)
    {
        $header = array(
            'Content-Type: application/json',
        );

        $ch = curl_init();
   
        curl_setopt($ch, CURLOPT_URL, self::PILIPAY_AUTOREGISTER_URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode($array));
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
