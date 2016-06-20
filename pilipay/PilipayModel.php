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
 * Class PilipayModel
 * -- provide a basic access of properties, whose name is case insensitive.
 */
class PilipayModel
{
    protected $_properties = array();

    /**
     * @param array $properties
     */
    public function __construct($properties = array())
    {
        if (!empty($properties)) {
            $this->setProperties($properties);
        }
    }

    /**
     * @param $properties
     */
    public function setProperties($properties)
    {
        foreach ($properties as $name => $value) {
            $this->{$name} = $value;
        }
    }

    /**
     * get a property
     * @param string $name property's name (case insensitive)
     * @return mixed property's value
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        } else {
            $name = Tools::strtolower($name);
            return $this->_properties[$name];
        }
    }

    /**
     * set a property
     * @param string $name property's name (case insensitive)
     * @param mixed $value property's value
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->{$setter}($value);
        } else {
            $name = Tools::strtolower($name);
            $this->_properties[$name] = $value;
        }
    }

    /**
     * verify whether all fields are OK
     * @throws PilipayError
     */
    public function verifyFields()
    {
        // check numeric fields
        foreach ($this->getNumericFieldNames() as $numericField) {
            $value = $this->{$numericField};
            // (null and '' equals 0)
            if (!is_numeric($value) && $value !== null && $value !== '') {
                throw new PilipayError(PilipayError::INVALID_ARGUMENT, array('name' => $numericField, 'value' => $value));
            } else {
                $this->{$numericField} = ($value ? (string)$value : 0);
            }
        }

        // check required fields
        foreach ($this->getRequiredFieldNames() as $requiredField) {
            if ($this->{$requiredField} === null) {
                throw new PilipayError(PilipayError::REQUIRED_ARGUMENT_NO_EXIST, array('name' => $requiredField, 'value'=> $this->{$requiredField}));
            }
        }
    }

    /**
     * @return array numberic fields
     */
    public function getNumericFieldNames()
    {
        return array();
    }

    /**
     * @return array required fields
     */
    public function getRequiredFieldNames()
    {
        return array();
    }
}
