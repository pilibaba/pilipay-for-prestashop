<?php

if (!defined('_PS_VERSION_'))
	exit;

/**
 * @param $module Pilipay
 * @return bool
 */
function upgrade_module_1_0_8($module)
{
	$hook_to_remove_id = Hook::getIdByName('advancedPaymentApi');
	if ($hook_to_remove_id) {
		$module->unregisterHook((int)$hook_to_remove_id);
	}
	return true;
}
