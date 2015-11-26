{if $status == 'ok'}
    <p>{l s='Your order on %s is complete.' sprintf=$shop_name mod='pilipay'}
		<br /><br /> {l s='Please send us a Pilipay with' mod='pilipay'}
		<br /><br />- {l s='Amount' mod='pilipay'} <span class="price"><strong>{$total_to_pay}</strong></span>
		<br /><br />- {l s='Name of account owner' mod='pilipay'}  <strong>{if $pilipayOwner}{$pilipayOwner}{else}___________{/if}</strong>
		<br /><br />- {l s='Include these details' mod='pilipay'}  <strong>{if $pilipayDetails}{$pilipayDetails}{else}___________{/if}</strong>
		<br /><br />- {l s='Bank name' mod='pilipay'}  <strong>{if $pilipayAddress}{$pilipayAddress}{else}___________{/if}</strong>
		{if !isset($reference)}
			<br /><br />- {l s='Do not forget to insert your order number #%d in the subject of your Pilibaba.' sprintf=$id_order mod='pilipay'}
		{else}
			<br /><br />- {l s='Do not forget to insert your order reference %s in the subject of your Pilibaba.' sprintf=$reference mod='pilipay'}
		{/if}		<br /><br />{l s='An email has been sent with this information.' mod='pilipay'}
		<br /><br /> <strong>{l s='Your order will be sent as soon as we receive payment.' mod='pilipay'}</strong>
		<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='pilipay'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='pilipay'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='pilipay'}
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='pilipay'}</a>.
	</p>
{/if}
