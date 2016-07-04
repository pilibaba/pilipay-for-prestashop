{*
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
*}


<div class="panel">
    <div class="row panel-header">
        <i class="icon-book"></i>
        MODE SELECT
    </div>
    <hr/>
    <div class="panel-content">
        <div class="row padding-top">
            <div class="col-md-6">
                <form id="mode_module_form" class="defaultForm test-mode"
                      action="#&amp;token={$smarty.get.token|escape:'html':'UTF-8'}" method="post">
                    <div class="form-group">
                        <label for="pili_mode">Merchant can select Test Mode to simulate payment with RMB.</label>
                        <select id="pili_mode" name="pili_mode" class="form-control">
                            {if $mode == '1'}
                                <option value="0">{l s='Live mode' mod='pilipay'}</option>
                                <option selected value="1">{l s='Test mode' mod='pilipay'}</option>
                            {else}
                                <option selected value="0">{l s='Live mode' mod='pilipay'}</option>
                                <option value="1">{l s='Test mode' mod='pilipay'}</option>
                            {/if}
                        </select>
                    </div>
                    <input type="submit" class="button btn btn-primary" id="module_form_submit_btn"
                           value="{l s='Set mode' mod='pilipay'}" name="submitMode">
                </form>
            </div>
        </div>
    </div>
</div>
