<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <form action="{$link->getModuleLink('pilipay', 'validation', [], true)|escape:'html':'UTF-8'}"
                  method="post" >
                <button type="submit"
                        title="{l s='Pay via Pilibaba (支持银联, 直邮中国)' mod='pilipay'}"
                        style="padding: 5px 10px; width: 100%; text-align: left">
                    <img src="{$this_path_bw}checkout.png"
                         alt="{l s='Pay via Pilibaba (支持银联, 直邮中国)' mod='pilipay'}"
                         style="height: 72px; width: auto;"/>
                    <span>请点击完成付款，支持银联卡，直邮至中国大陆全境</span>
                </button>
            </form>
        </p>
    </div>
</div>

