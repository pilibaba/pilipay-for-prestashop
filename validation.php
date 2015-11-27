<?php
/**
 * @deprecated 1.5.0 This file is deprecated, use moduleFrontController instead
 */

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../header.php');
include(dirname(__FILE__) . '/../../init.php');

$context = Context::getContext();

/**@var Pilipay $pilipay */
$pilipay = Module::getInstanceByName('pilipay');
$pilipay->performValidation($context);