{capture name=path}
    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}"
       title="{l s='Go back to the Checkout' mod='pilipay'}">{l s='Checkout' mod='pilipay'}</a>
    <span class="navigation-pipe">{$navigationPipe}</span>{l s='Pilibaba payment' mod='pilipay'}
{/capture}

<h1 class="page-heading">
    {l s='Order summary' mod='pilipay'}
</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="alert alert-warning">
        {l s='Your shopping cart is empty.' mod='pilipay'}
    </p>
{else}
    <form action="{$link->getModuleLink('pilipay', 'validation', [], true)|escape:'html':'UTF-8'}" method="post">
        <div class="box cheque-box">
            <h3 class="page-subheading">
                {l s='Pilibaba payment' mod='pilipay'}
            </h3>
            <p class="cheque-indent">
                <strong class="dark">
                    {l s='You have chosen to pay by Pilibaba.' mod='pilipay'} {l s='Here is a short summary of your order:' mod='pilipay'}
                </strong>
            </p>
            <p>
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
                    <div class="form-group">
                        <label>{l s='Choose one of the following:' mod='pilipay'}</label>
                        <select id="currency_payment" class="form-control" name="currency_payment">
                            {foreach from=$currencies item=currency}
                                <option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>
                                    {$currency.name}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                {else}
                    {l s='We allow the following currency to be sent via Pilibaba:' mod='pilipay'}&nbsp;<b>{$currencies.0.name}</b>
                    <input type="hidden" name="currency_payment" value="{$currencies.0.id_currency}" />
                {/if}
            </p>
            <p>
                - {l s='Please confirm your order by clicking "I confirm my order".' mod='pilipay'}
            </p>
        </div><!-- .cheque-box -->
        <p class="cart_navigation clearfix" id="cart_navigation">
            <a class="button-exclusive btn btn-default" href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}">
                <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='pilipay'}
            </a>
            <button class="button btn btn-default button-medium" type="submit">
                <span>{l s='I confirm my order' mod='pilipay'}<i class="icon-chevron-right right"></i></span>
            </button>
        </p>
    </form>
{/if}