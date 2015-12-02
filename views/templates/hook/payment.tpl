<div class="row">
    <div class="col-xs-12">
        <form action="{$link->getModuleLink('pilipay', 'validation', [], true)|escape:'html':'UTF-8'}"
              method="post">
            <p class="payment_module">
                <button type="submit"
                        title="{l s='Pay via Pilibaba (支持银联, 直邮中国)' mod='pilipay'}"
                        style="cursor: pointer; display: inline-block; padding: 0; margin: 0; border: none; width: auto; height: auto; text-align: center; background: none;"
                        >
                    <img src="{$this_path_bw}checkout.png"
                         alt="{l s='Pay via Pilibaba (支持银联, 直邮中国)' mod='pilipay'}"
                         style="height: 72px; width: auto;"/>
                    <span style="line-height: 72px; vertical-align: top">请点击完成付款，支持银联卡，直邮至中国大陆全境</span>
                </button>
            </p>
        </form>
    </div>
</div>

