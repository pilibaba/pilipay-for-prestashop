<?php

/**
 * @property Pilipay $module
 * 支付结果处理
 */
class PilipayResultModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function postProcess(){
        $this->module->processPayResult();
    }
}