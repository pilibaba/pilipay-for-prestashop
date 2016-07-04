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

// require all Pilipay's files
!class_exists('PilipayLogger', false) and require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PilipayLogger.php');
!class_exists('PilipayModel', false) and require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PilipayModel.php');
!class_exists('PilipayError', false) and require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PilipayError.php');
!class_exists('PilipayCurl', false) and require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PilipayCurl.php');
!class_exists('PilipayGood', false) and require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PilipayGood.php');
!class_exists('PilipayOrder', false) and require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PilipayOrder.php');
!class_exists('PilipayPayResult', false) and require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PilipayPayResult.php');
!class_exists('PilipayWarehouseAddress', false) and require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PilipayWarehouseAddress.php');
!class_exists('PilipayCurrency', false) and require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PilipayCurrency.php');
!class_exists('PilipayAutoregister', false) and require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PilipayAutoregister.php');
!class_exists('PilipayConfig', false) and require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PilipayConfig.php');
