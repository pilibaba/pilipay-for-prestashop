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

<!-- Button trigger modal -->

		<!-- Modal -->
		<div class="modal fade" id="autoRegisterForm" tabindex="-1" role="dialog" aria-labelledby="autoRegisterFormLabel">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel">Quick register with Pilibaba merchant number</h4>
		      </div>
		      <div class="modal-body">
			        <div class="row">
						<form id="auto_module_form" class="defaultForm auto-register" action="#&amp;token={$smarty.get.token|escape:'html':'UTF-8'}" method="post" enctype="multipart/form-data" onsubmit="return checkpass();">
					       		        
		
						         <div class="form-group">
								    <label for="pili_email">Email address</label>
								    <input type="email" class="form-control col-md-6" id="exampleInputEmail1" placeholder="Email" name="pili_email">
								  </div>
								  <div class="form-group">
								    <label for="pili_password">Password </label> <span id="pass_vali" style="color:red"></span>
								    <input type="text" class="form-control col-md-6" id="pili_password" placeholder="Password" name="pili_password">
								  </div>
								
								<label for="pili_currency">Currency</label><span class="help-tooltip" data-title="Select the currency used on your website and to withdraw to your bank account."><i class="icon-question-sign" aria-hidden="true" style="color:#00aff0"></i></span>   <span id="currency" style="color:red"></span>
						        <select id="pili_currency" name="pili_currency" class="form-control">
								<option value="">Select &nbsp&nbsp Currency</option>
						        {foreach from=$pili_currency item=v}
						        		<option value="{$v|escape:'html':'UTF-8'}">{$v|escape:'html':'UTF-8'}</option>
									
								{/foreach}
						        </select>								
								<label for="pili_warehouse_id">Warehouse</label><span class="help-tooltip" data-title="Select the nearest warehouse you will be shipping to. When you receive orders from Chinese customers (via Pilibaba gateway) you can deliver parcels to this warehouse."><i class="icon-question-sign" aria-hidden="true" style="color:#00aff0"></i></span>  <span id="warehouse" style="color:red"></span>
						        <select id="pili_warehouse_id" name="pili_warehouse_id" class="form-control">
								<option value="">Select  &nbsp&nbsp Warehouse</option>
						        {foreach from=$pili_address item=v}
						        	
						        		<option value="{$v['id']|escape:'html':'UTF-8'}">{$v['name']|escape:'html':'UTF-8'}</option>
									
								{/foreach}
						        </select>
								<br />
					            <input type="submit" class="button btn btn-primary" id="module_form_submit_btn" value="{l s='Quick register' mod='pilipay'}" name="submitRegister">
				
						</form>				
			        </div>

		      </div>
		    </div>
		  </div>
		</div>
		<script>
			function checkpass() 
			{
				if($("#pili_password").val().length < 8) {
					
					$("#pass_vali").html('{l s='Password length at least 8 chars ' mod='pilipay'}')
					return false;
				}
				if($("#pili_currency option:selected").val()==""){
					$("#currency").html('{l s='you have to choose a Currency ' mod='pilipay'}')
					return false;
				}
				if($("#pili_warehouse_id option:selected").val()==""){
					$("#warehouse").html('{l s='you have to choose a pili_warehouse ' mod='pilipay'}')
					return false;
				}
			}
		</script>