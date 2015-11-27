<?php
/**
 * @since 1.5.0
 * @property Pilipay $module
 */
class PilipayValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        Pilipay::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));

        $this->module->performValidation($this->context);
    }
}
