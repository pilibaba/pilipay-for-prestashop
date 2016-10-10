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
<div class="row">
    <div class="col-xs-12 col-md-12">

		<p class="payment_module" id="pilipay_payment_button">
            <a href="{$link->getModuleLink('pilipay', 'validation', [], true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay with Pilipay' mod='pilipay'}" data-method="post">
                <span class="label_en">
                    {l s='Pay with Pilibaba' mod='pilipay'}<br />
                </span>
                <span>
                    {l s='使用Pilbaba 中文收银台服务，支持银联卡，直邮至中国大陆全境，请确保第四步选择PiliExpress的物流方式.' mod='pilipay'}
                </span>
            </a>
		</p>
    </div>
</div>

