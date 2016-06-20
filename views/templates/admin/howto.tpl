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
		<i class="icon-cogs"></i>							
		How to setup pilipay module ?
	</div>
	<hr />
	<div class="panel-content">
		<div class="row padding-top">
			<div class="col-md-12">
				<ol>
					<li>
						<h4>Set your pilibaba's Merchant number and Secrect Key, then choose a nearest pilibaba's warehouse.</h4><br />
						<img style="max-width:80%" src="{$module_dir|escape:'html':'UTF-8'}views/img/merchant_detail.png" class="pictos" /><br/>
							
					</li>
					<br />
					<li>
						<h4>Add a new carrier for Chinese customers who using pilipay payment.</h4><br />
						<img style="max-width:80%" src="{$module_dir|escape:'html':'UTF-8'}views/img/create_new_carrier.png" class="pictos" /><br/>
						<h4>use the recommended value:</h4>
						1. Paste "<span style="color:#ee1010"> Pilibaba express 送货至中国 (如选择 Piliexpress, 请在下一步选择 Pilibaba 支付) </span>" in position 1; <br />
						2. Paste "<span style="color:#ee1010"> 7〜14天直邮中国 </span>"  in position 2 ; <br />
						3. Upload the logo  &nbsp;
						<img src="{$module_dir|escape:'html':'UTF-8'}views/img/Piliexpress.png" class="pictos" /> in position 3;<br/><br />
						<img style="max-width:80%" src="{$module_dir|escape:'html':'UTF-8'}views/img/carrier_setting.png" class="pictos" /><br/>
						
					</li>
					<br />
					<li>
						<h4>Then set the shipping cost according to your good's size, weight, group access and location to pilibaba's warehouse.</h4>
					</li>
					<br />
					<li>
						<h4>When you deliver the order package to pilibaba's warehouse, don't forget to paste the barcode to the package. You can get the barcode from order's detail.<h4> <br />
						<img style="max-width:80%" src="{$module_dir|escape:'html':'UTF-8'}views/img/bar_code.png" class="pictos" /><br/>
					</li>
				</ol>
			
			</div>
			
		</div>

	</div>	
	

</div>
