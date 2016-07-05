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
 * Class PilipayWarehouseAddress
 * This class helps to query all warehouse addresses of pilibaba
 *
 * @property string $country        - the country of the warehouse
 * @property string $countryCode    - the country code of the warehouse @see https://en.wikipedia.org/wiki/ISO_3166-1
 * @property string $firstName      - the first name of the receiver
 * @property string $lastName       - the last name of the receiver
 * @property string $address        - the address of the warehouse
 * @property string $city           - the city name of the warehouse
 * @property string $state          - the state name of the warehosue
 * @property string $zipcode        - the zipcode/postcode of the warehouse
 * @property string $tel            - the telephone number of the receiver
 */
class PilipayWarehouseAddress
{
    const WAREHOUSE_ADDRESS_PATH = 'http://www.pilibaba.com/pilipay/getAddressList';
    const PILIPAY_WAREHOUSES = 'PILIPAY_WAREHOUSES';

    /**
     * query all warehouse addresses
     * @params $resultFormat string objectList or arrayList
     * @return array
     */
    public static function queryAll()
    {
        $result = Tools::file_get_contents(self::WAREHOUSE_ADDRESS_PATH);
        if (empty($result)) {
            return array();
        }

        $array = Tools::jsonDecode($result, true);

        return $array;
    }

    /**
     * Format address
     * @return array
     */
    public static function addressFormat()
    {
        $addresses    = self::queryAll();
        $newAddresses = array();
        foreach ($addresses as $value) {
            $newAddresses[] = array(
                'id'   => $value['id'],
                'name' => $value['state'].' '.$value['city'].' '.$value['address'].' / '.$value['country'].';',
            );
        }

        return $newAddresses;
    }

    /*
        $val:需要查询的值
        $k  :需要查询的键名
    */
    public static function getWarehouseAddressBy($val, $k = 'id')
    {
        $addressList = self::queryAll();
        foreach ($addressList as $value) {
            if ($value[$k] == $val) {
                return $value;
            }
        }

        return null;
    }

    public static function shippingAddressFormat()
    {
        $warehouseId = Tools::getValue(self::PILIPAY_WAREHOUSES, Configuration::get(self::PILIPAY_WAREHOUSES));

        return self::getWarehouseAddressBy($warehouseId);
    }

    public static function addShippingAddress()
    {
        //判断Warehouse是否存在state ID
        if (!self::getStateId()) {
            return 'NOWAEWHOUSESTATEID';
        }
        if (!self::getCountryId()) {
            return 'NOWAREHOUSECOUNTRYID';
        }
    }

    //获取Warehouse的state ID
    public static function getStateId()
    {
        return State::getIdByIso(self::shippingAddressFormat()['isoStateCode']);
    }

    //获取Warehouse的country ID
    public static function getCountryId()
    {
        return Country::getByIso(self::shippingAddressFormat()['iso2CountryCode']);
    }
}
