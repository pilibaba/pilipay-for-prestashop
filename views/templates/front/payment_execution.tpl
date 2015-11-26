{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='pilipay'}">{l s='Checkout' mod='pilipay'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Pilibaba payment' mod='pilipay'}
{/capture}

{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='pilipay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='pilipay'}</p>
{else}

<h3>{l s='Pilibaba payment' mod='pilipay'}</h3>
<form action="{$link->getModuleLink('pilipay', 'validation', [], true)|escape:'html'}" method="post">
<p>
	<img src="{$this_path_bw}powered-by-pilibaba.png" alt="{l s='Pilipay' mod='pilipay'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='You have chosen to pay by Pilibaba.' mod='pilipay'}
	<br/><br />
	{l s='Here is a short summary of your order:' mod='pilipay'}
</p>
<p style="margin-top:20px;">
	- {l s='The total amount of your order is' mod='pilipay'}
	<span id="amount" class="price">{displayPrice price=$total}</span>
	{if $use_taxes == 1}
    	{l s='(tax incl.)' mod='pilipay'}
    {/if}
</p>
<p>
	-
	{if $currencies|@count > 1}
		{l s='We allow several currencies to be sent via Pilibaba.' mod='pilipay'}
		<br /><br />
		{l s='Choose one of the following:' mod='pilipay'}
		<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
			{foreach from=$currencies item=currency}
				<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
			{/foreach}
		</select>
	{else}
		{l s='We allow the following currency to be sent via Pilibaba:' mod='pilipay'}&nbsp;<b>{$currencies.0.name}</b>
		<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
	{/if}
</p>
<p>
	{l s='Pilibaba account information will be displayed on the next page.' mod='pilipay'}
	<br /><br />
	<b>{l s='Please confirm your order by clicking "I confirm my order".' mod='pilipay'}</b>
</p>
<p class="cart_navigation" id="cart_navigation">
	<input type="submit" value="{l s='I confirm my order' mod='pilipay'}" class="exclusive_large" />
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='pilipay'}</a>
</p>
</form>
{/if}
