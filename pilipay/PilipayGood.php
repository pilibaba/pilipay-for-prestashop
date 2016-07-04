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
 * Class PilipayGood
 * This class represent for a good in Pilipay.
 * It's used when adding goods to an order.
 *
 * - required fields:
 * @property $name          string      the product's name
 * @property $pictureUrl    string      the URL for the product's main picture, which would be displayed on the order page for customers
 * @property $price         number      the price of the product, including taxes. Its unit is the same with the currencyType of the order object.
 * @property $productUrl    string      the URL for the product. It must be available so that customers could go back to confirm the product's information and buy again.
 * @property $productId     string      the ID for the product. It must be unique in your shop, so that we can use it to identify the product.
 * @property $quantity      int         it is how many this product is in the order.
 * @property $weight        number      the weight of this single product.
 * @property $weightUnit    string      the unit of the weight. i.e: g/kg/lb(lbs)/oz
 *
 * - optional fields:
 * @property $attr          string      the product's attributes, like: color, size...
 * @property $category      string      the product's category when taxing
 * @property $height        string      reserved for future usage
 * @property $length        string      reserved for future usage
 * @property $width         string      reserved for future usage
 */
class PilipayGood extends PilipayModel
{
    const DEFAULT_PICTURE_URL = 'https://api.pilibaba.com/static/img/default-product.jpg';

    /**
     * Convert the object into an array format required in the HTTP API
     * 转换为API中的那种array表示形式
     * @return array
     */
    public function toApiArray()
    {
        $this->pictureUrl = $this->pictureUrl ? $this->pictureUrl : self::DEFAULT_PICTURE_URL;

        parent::verifyFields();

        return array_map('strval', array(
            // required:
            'name'       => $this->name,
            'pictureURL' => $this->pictureUrl,
            'price'      => (int)round($this->price * 100), // API: need a price in cent (in order.currencyType)
            'productURL' => $this->productUrl,
            'productId'  => $this->productId,
            'quantity'   => (int)round($this->quantity),
            'weight'     => (int)round(self::convertWeightToGram($this->weight, $this->weightUnit)),

            // optional:
            'attr'       => $this->attr,
            'category'   => $this->category,
            'height'     => $this->height,
            'length'     => $this->length,
            'width'      => $this->width,
        ));
    }

    /**
     * Convert the weight into grams -- which is the unit of the HTTP API.
     * 将重量转换为以克为单位的数值
     * @param $amount
     * @param $unit
     * @return mixed
     * @throws PilipayError
     */
    public static function convertWeightToGram($amount, $unit)
    {
        switch (Tools::strtolower($unit)) {
            case 'g':
                return $amount;
            case 'kg':
                return $amount * 1000;
            case 'oz':
                return $amount * 28.3495231; // 1盎司(oz)=28.3495231克(g)
            case 'lb':
            case 'lbs':
                return $amount * 453.59237; // 1磅(lb)=453.59237克(g)
            default:
                throw new PilipayError(PilipayError::INVALID_ARGUMENT, array('name' => 'weightUnit', 'value' => $unit));
        }
    }

    public function getRequiredFieldNames()
    {
        return array('name', 'pictureUrl', 'price', 'productUrl', 'productId', 'quantity', 'weight', 'weightUnit');
    }

    public function getNumericFieldNames()
    {
        return array('price', 'weight', 'height', 'length', 'width');
    }
}
