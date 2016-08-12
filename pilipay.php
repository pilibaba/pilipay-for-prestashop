<?php
/**
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_.'pilipay/pilipay/autoload.php';

class Pilipay extends PaymentModule
{
    const     DEBUG_MODE = false; // is to debug?
    const     LOG_FILE = '/log/pilipay.log';

    protected $_html = '';
    protected $_postErrors = array();

    public $merchantNo;
    public $appSecret;
    public $currency;
    public $warehouse;
    public $testmode;

    const PILIPAY_TESTMODE = 'PILIPAY_TESTMODE';
    const PILIPAY_MERCHANT_NO = 'PILIPAY_MERCHANT_NO';
    const PILIPAY_APP_SECRET = 'PILIPAY_APP_SECRET';
    const PILIPAY_CURRENCY = 'PILIPAY_CURRENCY';
    const PILIPAY_WAREHOUSES = 'PILIPAY_WAREHOUSES';
    const OS_AWAITING = 'PILIPAY_OS_AWAITING'; // order status: awaiting pilibaba payment
    const OS_PAID = 'PS_OS_PAYMENT'; // order status: paid
    const OS_ERROR = 'PS_OS_ERROR'; // order status: with error
    const OS_REFUND = 'PS_OS_REFUND'; // order status: refund

    /**
     * Module constructor
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name                   = 'pilipay';
        $this->tab                    = 'payments_gateways';
        $this->version                = '1.2.6';
        $this->author                 = 'Pilibaba';
        $this->controllers            = array('payment', 'validation');
        $this->is_eu_compatible       = 0; // = 1; todo: what should I do to be compatible with EU?
        $this->ps_versions_compliancy = array('min' => '1.5.1', 'max' => '1.6.1.5',);
        $this->module_key             = '1d52b16e6ed130c60b22ac7896f69bd2';
        $this->currencies             = true;
        $this->currencies_mode        = 'checkbox';

        $config = Configuration::getMultiple(
            array(
                self::PILIPAY_MERCHANT_NO,
                self::PILIPAY_APP_SECRET,
                self::PILIPAY_WAREHOUSES,
                self::PILIPAY_TESTMODE,
            )
        );
        if (!empty($config[self::PILIPAY_MERCHANT_NO])) {
            $this->merchantNo = $config[self::PILIPAY_MERCHANT_NO];
        }
        if (!empty($config[self::PILIPAY_APP_SECRET])) {
            $this->appSecret = $config[self::PILIPAY_APP_SECRET];
        }
        if (!empty($config[self::PILIPAY_CURRENCY])) {
            $this->currency = $config[self::PILIPAY_CURRENCY];
        }
        if (!empty($config[self::PILIPAY_WAREHOUSES])) {
            $this->warehouse = $config[self::PILIPAY_WAREHOUSES];
        }
        if (!empty($config[self::PILIPAY_TESTMODE])) {
            $this->testmode = $config[self::PILIPAY_TESTMODE];
        }
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName      = $this->l('Pilipay');
        $this->description      = $this->l('Pilibaba All-in-One gateway provides a unique combined Payment & Logistics solution & Customs compliance for eCommerce platforms to China market. By using Pilibaba service, merchants will be able to sell to 1.3 Billion Chinese customers instantly.Here are core benefits from Pilibaba for both merchants and Chinese customers.');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');
        if (empty($this->merchantNo) || empty($this->appSecret)) {
            $this->warning = $this->l('Merchant number and secret key must be configured before using this module.');
        }
        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
        PilipayLogger::instance()->setHandler(array(__CLASS__, 'log'));
    }

    /**
     * Install this module. Register hooks and initiate.
     * @return bool success or not
     * @throws PrestaShopException
     */
    public function install()
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, Tools::jsonEncode(func_get_args())));
        if (!parent::install()
            || !$this->registerHook('header')
            || !$this->registerHook('payment')
            || !$this->registerHook('actionAdminOrdersTrackingNumberUpdate')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('shoppingcart')
            || !$this->registerHook('displayAdminOrder')
            || !$this->createOrderStates()
        ) {
            return false;
        }

        return true;
    }

    /**
     * uninstall
     * @return bool
     */
    public function uninstall()
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, Tools::jsonEncode(func_get_args())));
        if (!Configuration::deleteByName(self::PILIPAY_MERCHANT_NO)
            || !Configuration::deleteByName(self::PILIPAY_APP_SECRET)
            || !Configuration::deleteByName(self::PILIPAY_CURRENCY)
            || !Configuration::deleteByName(self::PILIPAY_WAREHOUSES)
            || !Configuration::deleteByName(self::PILIPAY_TESTMODE)
            || !parent::uninstall()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Pilipay Configuration Page
     */
    public function getContent()
    {
        // Test Mode
        if (((bool)Tools::isSubmit('submitMode')) == true) {
            $this->postProcessMode();
        }
        $mode = Configuration::get(self::PILIPAY_TESTMODE);
        $this->context->smarty->assign('mode', $mode);

        // Auto Register
        if (((bool)Tools::isSubmit('submitRegister')) == true) {
            $this->postProcessRegister();
        }
        $configured = Configuration::get(self::PILIPAY_MERCHANT_NO);
        $this->context->smarty->assign('configured', $configured);

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('registerurl', $this->context->link->getModuleLink('pilipay', 'autoregister', array(), true));
        $this->context->smarty->assign('pili_currency', PilipayCurrency::arrayFormat());
        $this->context->smarty->assign('pili_address', PilipayWarehouseAddress::addressFormat());

        $output       = $this->context->smarty->fetch($this->local_path.'views/templates/admin/backend.tpl');
        $testmode     = $this->context->smarty->fetch($this->local_path.'views/templates/admin/testmode.tpl');
        $howto        = $this->context->smarty->fetch($this->local_path.'views/templates/admin/howto.tpl');
        $registerForm = $this->context->smarty->fetch($this->local_path.'views/templates/admin/autoregister.tpl');

        $addressResult = PilipayWarehouseAddress::addShippingAddress();
        if ($addressResult == 'NOWAREHOUSECOUNTRYID') {
        }
        if ($addressResult == 'NOWAEWHOUSESTATEID') {
        }

        self::log(sprintf("Calling %s with %s", __METHOD__, Tools::jsonEncode(func_get_args())));
        if (Tools::isSubmit('btnSubmit')) {
            $this->verifyConfigFromPost();
            if (!count($this->_postErrors)) {
                $this->saveConfigFromPost();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }
        if ($mode != '1') {
            $this->_html .= $this->renderConfigForm();
        }

        return $output.$registerForm.$testmode.$this->_html.$howto;
    }

    /**
     * choose Mode Live, Test
     */
    public function postProcessMode()
    {
        Configuration::updateValue(self::PILIPAY_TESTMODE, Tools::getValue('pili_mode'));
        $this->_html .= $this->displayConfirmation($this->l('Pilipay Mode changed!'));
    }

    /**
     * solve auto register data
     */
    public function postProcessRegister()
    {
        $platformNo = pSQL(PilipayAutoregister::PLATFORM_NO);
        $secure_key = pSQL(PilipayAutoregister::SECRECT_KEY);
        $logistics  = pSQL(trim(Tools::getValue('pili_warehouse_id')));
        $email      = pSQL(trim(Tools::getValue('pili_email')));
        $currency   = pSQL(trim(Tools::getValue('pili_currency')));
        $password   = pSQL(trim(Tools::getValue('pili_password')));
        $data       = array(
            'platformNo' => $platformNo,
            'appSecret'  => Tools::strtoupper(md5($logistics.$platformNo.$secure_key.$currency.$email.md5($password))),
            'email'      => $email,
            'password'   => md5($password),
            'currency'   => $currency,
            'logistics'  => $logistics,
        );
        $response = PilipayAutoregister::register($data);
        $this->saveRegisterResponse($response);
    }

    /**
     * save autoregister result to prestashop
     *
     * @param $json
     */
    protected function saveRegisterResponse($json)
    {
        $array = Tools::jsonDecode($json, true);

        if ($array['code'] == '0') {
            //注册成功
            if (Tools::isSubmit('submitRegister')) {
                Configuration::updateValue(self::PILIPAY_MERCHANT_NO, $array['data']['merchantNo']);
                Configuration::updateValue(self::PILIPAY_APP_SECRET, $array['data']['privateKey']);
                Configuration::updateValue(self::PILIPAY_WAREHOUSES, Tools::getValue('pili_warehouse_id'));
                Configuration::updateValue(self::PILIPAY_CURRENCY, Tools::getValue('pili_currency'));
            }
            $this->_html .= $this->displayConfirmation($this->l('Pilibaba merchant account auto registered!'));
        } else {
            $this->_html .= $this->displayError($this->l($array['message']));
        }
    }


    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path.'/views/css/pilipay_form.css');
    }

    public function hookShoppingcart($params)
    {
        $this->smarty->assign(array());
    }

    public function hookPayment($params)
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, Tools::jsonEncode(func_get_args())));
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        if (!Configuration::get(self::PILIPAY_MERCHANT_NO) || !Configuration::get(self::PILIPAY_APP_SECRET)) {
            return;
        }

        $this->smarty->assign(array(
            'this_path'     => $this->_path,
            'this_path_bw'  => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
        ));

        // when click: POST to pilipay/controllers/front/validation
        // then to Pilipay::performValidation
        // to submit order to pilibaba.
        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentReturn($params)
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, Tools::jsonEncode(func_get_args())));

        if (!$this->active) {
            return;
        }

        $order = $params['objOrder'];
        $state = $order->getCurrentState();

        if (in_array($state, array(
            Configuration::get('PS_OS_PAYMENT'),
            Configuration::get('PS_OS_BANKWIRE'),
            Configuration::get('PS_OS_OUTOFSTOCK'),
            Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'),
        ))) {
            $this->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
                'status'       => 'ok',
                'id_order'     => $params['objOrder']->id,
            ));

            if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference)) {
                $this->smarty->assign('reference', $params['objOrder']->reference);
            }
        } else {
            $this->smarty->assign('status', 'failed');
        }

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function hookActionAdminOrdersTrackingNumberUpdate($params)
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, Tools::jsonEncode(func_get_args())));

        try {
            /**@var $order Order */
            $order          = $params['order'];
            $trackingNumber = $order->shipping_number;

            $pilipayOrder             = new PilipayOrder();
            $pilipayOrder->merchantNO = Configuration::get(self::PILIPAY_MERCHANT_NO);

            if ($this->testmode == '1') {
                PilipayConfig::setUseProductionEnv(false);
                $pilipayOrder             = new PilipayOrder();
                $pilipayOrder->merchantNO = pSQL('0210000202');
            }

            $pilipayOrder->orderNo = $order->id;
            $pilipayOrder->updateTrackNo($trackingNumber);
        } catch (PilipayError $e) {
            self::log('error', $e->getMessage());
        }
    }

    public function hookDisplayAdminOrder($order)
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, get_class(reset($order))));
        $orderId = $order['id_order'];

        $order = new Order($orderId);
        if (strcasecmp($order->payment, $this->name) !== 0) {
            return null;
        }

        if ($this->testmode == '1') {
            PilipayConfig::setUseProductionEnv(false);
            $pilipayOrder             = new PilipayOrder();
            $pilipayOrder->merchantNO = pSQL('0210000202');
        } else {
            $pilipayOrder             = new PilipayOrder();
            $pilipayOrder->merchantNO = Configuration::get(self::PILIPAY_MERCHANT_NO);
        }

        $pilipayOrder->orderNo = $orderId;
        $barcodePicUrl         = $pilipayOrder->getBarcodePicUrl();
        $barcodeFileName       = "barcode-for-order-{$orderId}.jpg";

        $this->context->smarty->assign('barcodePicUrl', $barcodePicUrl);
        $this->context->smarty->assign('barcodeFileName', $barcodeFileName);

        if (defined('_PS_VERSION_') and version_compare(_PS_VERSION_, '1.6') > 0) {
            $html = $this->context->smarty->fetch($this->local_path.'views/templates/admin/displayadminorder.tpl');
        } else {
            $html = $this->context->smarty->fetch($this->local_path.'views/templates/admin/displayadmorder.tpl');
        }

        return $html;
    }

    // create order status
    private function createOrderStates()
    {
        if (!Configuration::get(self::OS_AWAITING)) {
            $os       = new OrderState();
            $os->name = array();

            foreach (Language::getLanguages(false) as $language) {
                $os->name[(int)$language['id_lang']] = 'Awaiting pilibaba payment';
            }

            $os->color      = '#4169E1';
            $os->hidden     = false;
            $os->send_email = false;
            $os->delivery   = false;
            $os->logable    = false;
            $os->invoice    = false;
            $os->paid       = false;

            if ($os->add()) {
                Configuration::updateValue(self::OS_AWAITING, $os->id);
                copy(dirname(__FILE__).'/logo.png', dirname(__FILE__).'/../../img/os/'.(int)$os->id.'.png');
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * 检查货币类型.下订单过程中,选择支付方式前后会调用以核对
     *
     * @param $cart
     *
     * @return bool
     */
    public function checkCurrency($cart)
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, Tools::jsonEncode(func_get_args())));
        $currency_order    = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    // 渲染[设置]页面的下方的表单,用于设置内容
    protected function renderConfigForm()
    {
        $options  = PilipayWarehouseAddress::addressFormat();
        $currency = PilipayCurrency::selectFormat();

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Merchant details'),
                    'icon'  => 'icon-envelope',
                ),
                'input'  => array(
                    array(
                        'type'     => Tools::getValue(self::PILIPAY_MERCHANT_NO, Configuration::get(self::PILIPAY_MERCHANT_NO)) ? 'free' : 'text',
                        'label'    => $this->l('Merchant number'),
                        'name'     => self::PILIPAY_MERCHANT_NO,
                        'required' => true,
                    ),
                    array(
                        'type'     => Tools::getValue(self::PILIPAY_APP_SECRET, Configuration::get(self::PILIPAY_APP_SECRET)) ? 'free' : 'text',
                        'label'    => $this->l('Secret key'),
                        'name'     => self::PILIPAY_APP_SECRET,
                        'required' => true,
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Currency'),
                        'name'     => self::PILIPAY_CURRENCY,
                        'required' => true,
                        'options'  => array(
                            'query' => $currency,
                            'id'    => 'id',
                            'name'  => 'currency',
                        ),
                    ),

                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Warehouses'),
                        'name'     => self::PILIPAY_WAREHOUSES,
                        'required' => true,
                        'options'  => array(
                            'query' => $options,
                            'id'    => 'id',
                            'name'  => 'name',
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $lang                             = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id                       = (int)Tools::getValue('id_carrier');
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'btnSubmit';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token                    = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars                 = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    /**
     * getConfigFieldsValues in Pilipay Configuration Pages
     * @return array
     */
    public function getConfigFieldsValues()
    {
        $PILIPAY_MERCHANT_NO = Tools::getValue(self::PILIPAY_MERCHANT_NO, Configuration::get(self::PILIPAY_MERCHANT_NO));
        $PILIPAY_APP_SECRET  = Tools::getValue(self::PILIPAY_APP_SECRET, Configuration::get(self::PILIPAY_APP_SECRET));
        $PILIPAY_CURRENCY    = Tools::getValue(self::PILIPAY_CURRENCY, Configuration::get(self::PILIPAY_CURRENCY));
        $PILIPAY_WAREHOUSES  = Tools::getValue(self::PILIPAY_WAREHOUSES, Configuration::get(self::PILIPAY_WAREHOUSES));
        $this->context->smarty->assign('PILIPAY_MERCHANT_NO', $PILIPAY_MERCHANT_NO);
        $MERCHANT_NO = $this->context->smarty->fetch($this->local_path.'views/templates/admin/merchant_no.tpl');
        $this->context->smarty->assign('PILIPAY_APP_SECRET', $PILIPAY_APP_SECRET);
        $APP_SECRET = $this->context->smarty->fetch($this->local_path.'views/templates/admin/app_secret.tpl');

        return array(
            self::PILIPAY_MERCHANT_NO => $PILIPAY_MERCHANT_NO ? $MERCHANT_NO : $PILIPAY_MERCHANT_NO,
            self::PILIPAY_APP_SECRET  => $PILIPAY_APP_SECRET ? $APP_SECRET : $PILIPAY_APP_SECRET,
            self::PILIPAY_CURRENCY    => $PILIPAY_CURRENCY,
            self::PILIPAY_WAREHOUSES  => $PILIPAY_WAREHOUSES,

        );
    }

    /**
     * 结账页面, 验证购物车
     *
     * @param $context Context
     */
    public function performValidation($context)
    {

        $cart = $context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed his address
        // just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'pilipay') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->l('This payment method is not available.', 'validation'));
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $context->currency;
        $total    = (float)$cart->getOrderTotal(true, Cart::BOTH);

        // 修改订单状态为待从pilibaba支付 validateOrder method save an order to database.
        $this->validateOrder($cart->id, Configuration::get(self::OS_AWAITING), $total, $this->displayName, null, null, (int)$currency->id, false, $customer->secure_key);

        // 支付完成后的回调URL
        $paidCallbackUrl = $this->context->link->getModuleLink($this->name, 'result', [], true);
        $pageUrl         = self::getHttpHost().__PS_BASE_URI__.'index.php?controller=history';

        $order = new Order($this->currentOrder);

        //以下几行代码用来修改订单地址到 pilibaba warehouse。
        $id_address = $this->newAddress($order);
        $sql        = 'UPDATE `'._DB_PREFIX_.'orders` 
                       SET `id_address_delivery` ='.(int)$id_address.',`id_address_invoice` = '.(int)$id_address.'
                       WHERE id_order='.(int)$this->currentOrder;
        Db::getInstance()->execute($sql);

        if (!Validate::isLoadedObject($order)) {
            die($this->l('This order is invalid.', 'pilipay'));
        }

        try {
            // create an order
            if ($this->testmode == '1') {
                PilipayConfig::setUseProductionEnv(false);
                $pilipayOrder             = new PilipayOrder();
                $pilipayOrder->merchantNO = pSQL('0210000202');
                $pilipayOrder->appSecret  = pSQL('cbkmqa1s');
            } else {
                $pilipayOrder             = new PilipayOrder();
                $pilipayOrder->merchantNO = pSQL($this->merchantNo);
                $pilipayOrder->appSecret  = pSQL($this->appSecret);
            }

            $pilipayOrder->currencyType = pSQL($this->getAbbrOfCurrency($currency));
            $pilipayOrder->orderNo      = pSQL($order->id);
            $pilipayOrder->orderAmount  = $total;
            $pilipayOrder->orderTime    = date('Y-m-d H:i:s');
            $pilipayOrder->pageUrl      = pSQL($pageUrl); //self::_getHttpHost() . '/index.php?controller=history';
            $pilipayOrder->serverUrl    = pSQL($paidCallbackUrl);
            $pilipayOrder->redirectUrl  = pSQL($paidCallbackUrl);
            $pilipayOrder->shipper      = $order->total_shipping_tax_incl;

            $totalProductVatTax = 0;
            // create a good
            foreach ($order->getProducts() as $product) {
                $price    = $product['product_price'];
                $price_wt = $product['product_price_wt'];
                $totalProductVatTax += $price_wt - $price;

                $productObj        = new Product($product['product_id']);
                $productUrl        = $context->link->getProductLink($productObj);
                $productPictureUrl = null;
                if (!empty($product['image'])) {
                    $img = $product['image'];
                    if ($img instanceof Image) {
                        $productPictureUrl = $context->link->getImageLink($img->id_image, $img->id_image);
                    }
                }

                $pilipayGood             = new PilipayGood();
                $product['product_name'] = pSQL($product['product_name']);
                $pilipayGood->name       = $product['product_name'].(isset($product['attributes']) ? ' - '.$product['attributes'] : '');
                $pilipayGood->attr       = '';
                $pilipayGood->category   = '';
                $pilipayGood->pictureUrl = pSQL($productPictureUrl);
                $pilipayGood->price      = $price_wt;
                $pilipayGood->productUrl = pSQL($productUrl);
                $pilipayGood->productId  = pSQL($product['product_id']);
                $pilipayGood->quantity   = $product['product_quantity'];
                $pilipayGood->weight     = $product['product_weight'];
                $pilipayGood->weightUnit = 'kg'; // default kg for presta shop.
                $pilipayGood->width      = 0; // 10: cm -> mm
                $pilipayGood->height     = 0;// 10: cm -> mm
                $pilipayGood->length     = 0;// 10: cm -> mm

                // add the good to order
                $pilipayOrder->addGood($pilipayGood);
            }

            $pilipayOrder->tax = min(
                0,
                $cart->getOrderTotal(true) - $cart->getOrderTotal(false) - ($order->total_shipping_tax_incl - $order->total_shipping_tax_excl) - $totalProductVatTax
            );
            echo $pilipayOrder->renderSubmitForm();
            die;
        } catch (PilipayError $e) {
            self::log("error", $e->getMessage().PHP_EOL.$e->getTraceAsString());
            die($e->getMessage());
        } catch (Exception $e) {
            self::log("error", $e->getMessage().PHP_EOL.$e->getTraceAsString());
            die($e->getMessage());
        }
    }

    /**
     * make new address of PILIBABA WAREHOUSE assign to customer
     *
     * @param Order ,
     *
     * @return id, The address id which inserted into database;
     **/
    protected function newAddress($order)
    {
        $pilibbabAddress = PilipayWarehouseAddress::getWarehouseAddressBy(Tools::getValue(self::PILIPAY_WAREHOUSES, Configuration::get(self::PILIPAY_WAREHOUSES)));

        $AddressObject              = new Address();
        $AddressObject->id_customer = $order->id_customer;
        $AddressObject->firstname   = pSQL($pilibbabAddress['firstName']);
        $AddressObject->lastname    = pSQL($pilibbabAddress['lastName']);
        $AddressObject->address1    = pSQL($pilibbabAddress['address']);
        $AddressObject->city        = pSQL($pilibbabAddress['city']);
        $AddressObject->id_country  = pSQL(PilipayWarehouseAddress::getCountryId());
        $AddressObject->id_state    = pSQL(PilipayWarehouseAddress::getStateId());
        $AddressObject->phone       = pSQL($pilibbabAddress['tel']);
        $AddressObject->postcode    = pSQL($pilibbabAddress['zipcode']);
        $AddressObject->alias       = 'pilibaba';
        $AddressObject->add();

        return $AddressObject->id;
    }


    /**
     * process the pay result
     */
    public function processPayResult()
    {
        try {
            $payResult = PilipayPayResult::fromRequest();
            if (Configuration::get(self::PILIPAY_TESTMODE) == '1') {
                $secret = 'cbkmqa1s';
            } else {
                $secret = Configuration::get(self::PILIPAY_APP_SECRET);
            }
            if (!$payResult->verify($secret)) {
                $payResult->returnDealResultToPilibaba(400);
            }

            $order = new Order($payResult->orderNo);
            if (strcasecmp($order->payment, $this->name) !== 0) {
                $payResult->returnDealResultToPilibaba(401);
            }

            $orderState = $payResult->isSuccess() ? self::OS_PAID : self::OS_ERROR;
            self::log('info', "order {$order->id} is to be updated to {$orderState} via {$this->name}");

            $orderHistory           = new OrderHistory();
            $orderHistory->id_order = $order->id;
            $orderHistory->changeIdOrderState(Configuration::get($orderState), $order);
            $orderHistory->addWithemail();

            self::log('info', "order {$order->id} state updated to ".$orderState);

            $payResult->returnDealResultToPilibaba(1);
        } catch (Exception $e) {
            $payResult->returnDealResultToPilibaba($e->getCode());
        }
    }

    /**
     * @param Currency $currency
     *
     * @return string
     */
    public function getAbbrOfCurrency($currency)
    {
        return Tools::strtoupper($currency->iso_code);
    }

    // 后台[设置]页面中: 验证输入内容
    protected function verifyConfigFromPost()
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, Tools::jsonEncode(func_get_args())));
        $PILIPAY_MERCHANT_NO = Tools::getValue(self::PILIPAY_MERCHANT_NO, Configuration::get(self::PILIPAY_MERCHANT_NO));
        $PILIPAY_APP_SECRET  = Tools::getValue(self::PILIPAY_APP_SECRET, Configuration::get(self::PILIPAY_APP_SECRET));
        if (Tools::isSubmit('btnSubmit')) {
            if (!trim($PILIPAY_MERCHANT_NO)) {
                $this->_postErrors[] = $this->l('Merchant number is required.');
            } elseif (!trim($PILIPAY_APP_SECRET)) {
                $this->_postErrors[] = $this->l('Secret key is required.');
            }
        }
    }

    // 后台[设置]页面中: 保存数据
    protected function saveConfigFromPost()
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, Tools::jsonEncode(func_get_args())));
        $PILIPAY_MERCHANT_NO = Configuration::get(self::PILIPAY_MERCHANT_NO);
        $PILIPAY_APP_SECRET  = Configuration::get(self::PILIPAY_APP_SECRET);
        if (Tools::isSubmit('btnSubmit')) {
            if (!$PILIPAY_MERCHANT_NO) {
                Configuration::updateValue(self::PILIPAY_MERCHANT_NO, trim(Tools::getValue(self::PILIPAY_MERCHANT_NO)));
            }
            if (!$PILIPAY_APP_SECRET) {
                Configuration::updateValue(self::PILIPAY_APP_SECRET, trim(Tools::getValue(self::PILIPAY_APP_SECRET)));
            }
            Configuration::updateValue(self::PILIPAY_CURRENCY, trim(Tools::getValue(self::PILIPAY_CURRENCY, Configuration::get(self::PILIPAY_CURRENCY))));
            Configuration::updateValue(self::PILIPAY_WAREHOUSES, trim(Tools::getValue(self::PILIPAY_WAREHOUSES, Configuration::get(self::PILIPAY_WAREHOUSES))));
        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    protected function getHttpHost()
    {
        return Tools::getHttpHost(true);
    }

    public static function log($level, $msg = '')
    {
        if (!$msg) {
            list($level, $msg) = array('debug', $level);
        }

        if ($level == 'debug' && !self::DEBUG_MODE) {
            return;
        } elseif ($level == 'debug' && self::DEBUG_MODE) {
            self::logToFile($level, $msg);
        }

        try {
            if (class_exists('PrestaShopLogger')) {
                PrestaShopLogger::addLog('pilipay:'.$level.': '.$msg, 1, 0, 'pilipay', Configuration::get(self::PILIPAY_MERCHANT_NO));
            } elseif (class_exists('Logger')) {
                $msg = strtr($msg, array(
                    '{' => '&#123;',
                    '}' => '&#125;',
                    '<' => '&lt;',
                    '>' => '&gt;',
                ));
                Logger::addLog('pilipay:'.$level.': '.$msg, 1, 0, 'pilipay', Configuration::get(self::PILIPAY_MERCHANT_NO));
            }
        } catch (Exception $e) {
            if (self::DEBUG_MODE) {
                trigger_error(get_class($e).': '.$e->getMessage().PHP_EOL.$e->getTraceAsString(), E_USER_WARNING);
            }
        }
    }

    /**
     * record a log message
     *
     * @param string $level
     * @param string $msg
     */
    public static function logToFile($level, $msg = '')
    {
        $msg = date('Y-m-d H:i:s ').$level.' '.$msg.PHP_EOL;
        $msg .= sprintf(' -- %s %s with request: %s', $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], Tools::jsonEncode($_REQUEST));

        if (self::DEBUG_MODE) {
            $e = new Exception();
            $msg .= PHP_EOL.str_replace(realpath(dirname(__FILE__).'/../../').'/', '', $e->getTraceAsString());
        }
        // set log path as Relative path
        @file_put_contents(dirname(__FILE__).self::LOG_FILE, $msg.PHP_EOL, FILE_APPEND);
    }
}
