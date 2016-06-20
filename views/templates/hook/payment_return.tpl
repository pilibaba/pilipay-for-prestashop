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
{if $status == 'ok'}
    <p>{l s='Your order on %s is complete.' sprintf=$shop_name mod='pilipay'}
		<br /><br /> {l s='Please send us a Pilipay with' mod='pilipay'}
		<br /><br />- {l s='Amount' mod='pilipay'} <span class="price"><strong>{$total_to_pay|escape:'html':'UTF-8'}</strong></span>
		<br /><br />- {l s='Name of account owner' mod='pilipay'}  <strong>{if $pilipayOwner}{$pilipayOwner|escape:'html':'UTF-8'}{else}___________{/if}</strong>
		<br /><br />- {l s='Include these details' mod='pilipay'}  <strong>{if $pilipayDetails}{$pilipayDetails|escape:'html':'UTF-8'}{else}___________{/if}</strong>
		<br /><br />- {l s='Bank name' mod='pilipay'}  <strong>{if $pilipayAddress}{$pilipayAddress|escape:'html':'UTF-8'}{else}___________{/if}</strong>
		{if !isset($reference)}
			<br /><br />- {l s='Do not forget to insert your order number #%d in the subject of your Pilibaba.' sprintf=$id_order mod='pilipay'}
		{else}
			<br /><br />- {l s='Do not forget to insert your order reference %s in the subject of your Pilibaba.' sprintf=$reference mod='pilipay'}
		{/if}		<br /><br />{l s='An email has been sent with this information.' mod='pilipay'}
		<br /><br /> <strong>{l s='Your order will be sent as soon as we receive payment.' mod='pilipay'}</strong>
		<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='pilipay'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team' mod='pilipay'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='pilipay'}
		<a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team' mod='pilipay'}</a>.
	</p>
{/if}
