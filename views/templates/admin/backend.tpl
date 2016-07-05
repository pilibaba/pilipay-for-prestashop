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

{**
Sell to 1.3 Billion Chinese customers immediately

Pilibaba All-in-One gateway provides a unique combined Payment & Logistics solution & Customs compliance for eCommerce platforms to China market. By using Pilibaba service, merchants will be able to sell to 1.3 Billion Chinese customers instantly.
Here are core benefits from Pilibaba for both merchants and Chinese customers:

    Accept payments from 1.3 Billion Chinese shoppers
    Display landed price for Chinese shoppers. Incl. products price + duties and taxes + international air freight cost
    Locally Shipping - deliver locally to one of Pilibaba designated in-country warehouse, Pilibaba provides efficient label, customs clearance and door-to-door delivery anywhere in China
    5*8 local customer service for Chinese shoppers (email, chat and phone call)
**}
<div class="panel">
    <div class="row pilipay-header">
        <div class="col-xs-8 col-md-8">
            <p>
                <img src="{$module_dir|escape:'html':'UTF-8'}logo.png" style="max-width:15%" id="payment-logo"
                     class="pull-left"/>
            <h1 class=" text-center text-muted">
                {l s='Sell to 1.3 Billion Chinese customers immediately' mod='pilipay'}<br/>
            </h1>
            </p>

            <div style="clear:both;"></div>
            <div style="height:1em;display:block"></div>
            <p style="margin-top:3em">
                <strong>{l s='Pilibaba All-in-One gateway is fully integrated in Prestashop latest version that enables merchants easy selling goods to China and without any risk. Once the Chinese customers add products to shopping bag and choose Pilibaba checkout button, they are simply transferred to a localized checkout page to finalize their orders. The checkout page is tailored to China market, customers can view their guaranteed shipping, duties and taxes fees in their local currency. By using Pilibaba service, merchants will be able to sell to 1.3 Billion Chinese customers instantly.' mod='pilipay'}</strong>
                see our 1 minute demo <a target="_blank"
                                         href="https://www.youtube.com/watch?v=UvkA_rsmY14&feature=youtu.be"> video </a>

            </p><br/>
            <img src="{$module_dir|escape:'html':'UTF-8'}views/img/capitalflow_01.png" id="capitalflow_01"
                 style="max-width: 100%"/><br/>

            <p>

            <p class="justify">
                <strong>{l s='With Pilibaba support,your website is able to have below features for Chinese customers:' mod='pilipay'}</strong>
            </p>
            <p class="justify">
                {l s='1, Local and familiar shopping and payment experience on your site.' mod='pilipay'}
            </p>
            <p class="justify">
                {l s='2, Accept Chinese bank debit cards and credit cards' mod='pilipay'}
            </p>
            <p class="justify">
                {l s='3 ,Offer fast, reasonably priced shipping – provide efficient labeling, customs clearance and door-to-door delivery anywhere in China' mod='pilipay'}
            </p>
            <p class="justify">
                {l s='4, Brand awareness and drive traffic to your website' mod='pilipay'}
            </p>
            <br/>

            <p class="justify">
                <strong>
                    {l s=' With regarding of the shipment, all you need to do is to ship the goods to one of our designated warehouses in country. Pilibaba do the rest things. eg:international shipment, customs clearance and last mile delivery.' mod='pilipay'}</strong>
                <br/>
                <span style="color:#a40505">{l s='See all Pilibaba warehouses at ' mod='pilipay'}</span>
                <a href="http://en.pilibaba.com/addressList"> {l s=' Warehouse Address' mod='pilipay'}</a>
            </p>
            <img src="{$module_dir|escape:'html':'UTF-8'}views/img/capitalflow_02.png" id="capitalflow_02"
                 style="max-width: 100%"/>

            {if !$configured}
                <div style="background:#eee; padding: 2em; width:55%; margin-top:30px">
                    <br/>
                    <button type="button" data-toggle="modal" data-target="#autoRegisterForm"
                            style="background: url({$module_dir|escape:'html':'UTF-8'}views/img/signup.png) no-repeat center center; text-indent: -999em; min-width: 303px; height:52px;border: none">
                        <b>{l s='Quick active your Pilibaba Account' mod='pilipay'}</b>
                    </button>
                    <br/><br/>

                    Manual registration is available if auto registration is failed <a
                            href="http://en.pilibaba.com/regist?from=prestashop" style="text-decoration: underline"
                            target="_blank">{l s='Create an account' mod='pilipay'}</a>
                    <br/><br/>
                    {l s='Already have one?' mod='pilipay'}
                    <a href="http://en.pilibaba.com/account/login?from=prestashop"
                       target="_blank"> {l s='Log in' mod='pilipay'}</a> {l s='your Pilibaba account and paste your "Merchant Number" and "Secrect-Key" into below fields.' mod='pilipay'}

                    <br/><br/>
                    <a href="http://en.pilibaba.com/" style="text-decoration: underline;color:rgba(23, 37, 235, 0.96)"
                       target="_blank">{l s='Get more information ' mod='pilipay'}</a>
                    <br/><br/>
                </div>
            {/if}
        </div>
        <div class="col-xs-4 col-md-4">
            {if !$configured}
                <div style="background:#eee; padding: 2em">
                    <br/>
                    <button type="button" data-toggle="modal" data-target="#autoRegisterForm"
                            style="background: url({$module_dir|escape:'html':'UTF-8'}views/img/signup.png) no-repeat center center; text-indent: -999em; min-width: 303px; height:52px;border: none">
                        <b>{l s='Quick active your Pilibaba Account' mod='pilipay'}</b>
                    </button>
                    <br/><br/>

                    Manual registration is available if auto registration is failed <a
                            href="http://en.pilibaba.com/regist?from=prestashop" style="text-decoration: underline"
                            target="_blank">{l s='Create an account' mod='pilipay'}</a>
                    <br/><br/>
                    {l s='Already have one?' mod='pilipay'}
                    <a href="http://en.pilibaba.com/account/login?from=prestashop"
                       target="_blank"> {l s='Log in' mod='pilipay'}</a> {l s='your Pilibaba account and paste your "Merchant Number" and "Secrect-Key" into below fields.' mod='pilipay'}

                    <br/><br/>
                    <a href="http://en.pilibaba.com/" style="text-decoration: underline;color:rgba(23, 37, 235, 0.96)"
                       target="_blank">{l s='Get more information ' mod='pilipay'}</a>
                    <br/><br/>
                </div>
            {/if}
        </div>
    </div>
    <br/>
    <div class="pilipay-content">
        <div class="row padding-top">
            <div class="col-md-8">
            </div>
        </div>
        <br/>
        <div class="row">
        </div>
    </div>
</div>
			

