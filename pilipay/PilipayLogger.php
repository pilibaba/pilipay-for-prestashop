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
 * Class PilipayLogger
 * This class is used for customizing logging.
 *
 * For example:
 *
 *   // to record logs into a file:
 *   PilipayLogger::instance()->setHandler(function($level, $msg){
 *       file_put_contents('path/to/pilipay/log/file', sprintf('%s %s: %s'.PHP_EOL, date('Y-m-d H:i:s'), $level, $msg));
 *   });
 *
 */
class PilipayLogger
{
    /**
     * @param callable $handler function ($level, $msg)...
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param $level string error/info/debug...
     * @param $msg string
     */
    public function log($level, $msg)
    {
        if (!is_null($this->handler)) {
            call_user_func($this->handler, $level, $msg);
        }
    }

    /**
     * @return PilipayLogger
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new PilipayLogger();
        }

        return self::$instance;
    }

    protected static $instance;
    private $handler = null;
}
