<?php

/**
 * @property Pilipay $module
 * Interface of dealing pay result from pilibaba
 */
class PilipayResultModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function postProcess(){
        $this->module->processPayResult();
    }
}