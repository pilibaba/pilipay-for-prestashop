<?php

// todo: PHP 兼容性检查 -- 至少兼任5.2
// todo: 更新运单号

if (!defined('_PS_VERSION_'))
    exit;

require_once(dirname(__FILE__).'/pilipay/autoload.php');

class Pilipay extends PaymentModule
{
    protected $_html = '';
    protected $_postErrors = array();

    public $merchantNo;
    public $appSecret;

    const PILIPAY_MERCHANT_NO = 'PILIPAY_MERCHANT_NO';
    const PILIPAY_APP_SECRET = 'PILIPAY_APP_SECRET';
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
        $this->name = 'pilipay';

        $this->tab = 'payments_gateways';
        $this->version = '1.0';
        $this->author = 'Pilibaba';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 1; // todo what should I do to be compatible with EU?

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $config = Configuration::getMultiple(array(self::PILIPAY_MERCHANT_NO, self::PILIPAY_APP_SECRET));
        if (!empty($config[self::PILIPAY_MERCHANT_NO])) {
            $this->merchantNo = $config[self::PILIPAY_MERCHANT_NO];
        }
        if (!empty($config[self::PILIPAY_APP_SECRET])) {
            $this->appSecret = $config[self::PILIPAY_APP_SECRET];
        }

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Pilipay');
        $this->description = $this->l('Pilibaba payment gateway -- accept China Yuan and deal international shipping.');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');
        if (empty($this->merchantNo) || empty($this->appSecret)) {
            $this->warning = $this->l('Merchant number and secret key must be configured before using this module.');
        }

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }

        // set log handler
        PilipayLogger::instance()->setHandler(array(__CLASS__, 'log'));
    }

    /**
     * Install this module. Register hooks and initiate.
     * 安装模块, 注册各种钩子
     * @return bool success or not
     * @throws PrestaShopException
     */
    public function install()
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        if (!parent::install()
            || !$this->registerHook('payment')
            || !$this->registerHook('displayPaymentEU')
            || !$this->registerHook('paymentReturn')
            || !$this->_createOrderStates()){
            return false;
        }
        return true;
    }

    /**
     * 卸载,清空配置
     * @return bool
     */
    public function uninstall()
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        if (!Configuration::deleteByName(self::PILIPAY_MERCHANT_NO)
            || !Configuration::deleteByName(self::PILIPAY_APP_SECRET)
            || !parent::uninstall()
        )
            return false;
        return true;
    }

    /**
     * 控制面板[设置]页, GET/POST都会处理
     */
    public function getContent()
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        if (Tools::isSubmit('btnSubmit')) { // POST request
            $this->_verifyConfigFromPost();
            if (!count($this->_postErrors)) {
                $this->_saveConfigFromPost();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else { // GET request:
            $this->_html .= '<br />';
        }

        $this->_html .= $this->_renderConfigForm();
        $this->_html .= $this->display(__FILE__, 'config-helper.tpl'); // 帮助信息

        return $this->_html;
    }

    // [前台]下单时选择支付的方式页面
    // 返回: 本支付方式对应的选项的html
    public function hookPayment($params)
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        if (!$this->active)
            return;
        if (!$this->checkCurrency($params['cart']))
            return;

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));

        return $this->display(__FILE__, 'payment.tpl'); // todo: 这个样式有点小问题
    }

    // todo: ???
    public function hookDisplayPaymentEU($params)
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        if (!$this->active){
            return;
        }

        if (!$this->checkCurrency($params['cart'])){
            return;
        }

        $payment_options = array(
            'cta_text' => $this->l('Pay via Pilibaba'),
            'logo' => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/checkout.png'),
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
        );

        return $payment_options;
    }

    // todo: ???
    // 支付返回页面
    // URL: GET /index.php?controller=order-confirmation&id_cart=10&id_module=74&id_order=9&key=8e0c7339b2467173557e0aa17bf8bbb5
    public function hookPaymentReturn($params)
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));

        if (!$this->active) {
            return;
        }

        $state = $params['objOrder']->getCurrentState();

        // todo...
        if (in_array($state, array(Configuration::get('PS_OS_PAYMENT'), Configuration::get('PS_OS_BANKWIRE'), Configuration::get('PS_OS_OUTOFSTOCK'), Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')))) {
            $this->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
                'status' => 'ok',
                'id_order' => $params['objOrder']->id
            ));

            if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference)){
                $this->smarty->assign('reference', $params['objOrder']->reference);
            }
        } else {
            $this->smarty->assign('status', 'failed');
        }

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    // create order status
    // 创建订单状态
    private function _createOrderStates()
    {
        if (!Configuration::get(self::OS_AWAITING)) {
            $os = new OrderState();
            $os->name = array();

            foreach (Language::getLanguages(false) as $language) {
                $os->name[(int)$language['id_lang']] = 'Awaiting pilibaba payment';
            }

            $os->color = '#4169E1';
            $os->hidden = false;
            $os->send_email = false;
            $os->delivery = false;
            $os->logable = false;
            $os->invoice = false;
            $os->paid = false;

            if ($os->add()) {
                Configuration::updateValue(self::OS_AWAITING, $os->id);
                copy(dirname(__FILE__) . '/logo.gif', dirname(__FILE__) . '/../../img/os/' . (int)$os->id . '.gif');
            } else {
                return false;
            }
        }
        return true;
    }

    // 检查货币类型
    // -- 下订单过程中,选择支付方式前后会调用以核对
    public function checkCurrency($cart)
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module))
            foreach ($currencies_module as $currency_module)
                if ($currency_order->id == $currency_module['id_currency'])
                    return true;
        return false;
    }

    // 渲染[设置]页面的下方的表单,用于设置内容
    protected function _renderConfigForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Merchant details'),
                    'icon' => 'icon-envelope'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Merchant number'),
                        'name' => self::PILIPAY_MERCHANT_NO,
                        'required' => true
                    ),
                    array(
                        'type' => 'password',
                        'label' => $this->l('Secret key'),
                        'name' => self::PILIPAY_APP_SECRET,
                        'required' => true
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name .
            '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    // 在后台[设置]的页面: 获取配置数据
    public function getConfigFieldsValues()
    {
        return array(
            self::PILIPAY_MERCHANT_NO => Tools::getValue(self::PILIPAY_MERCHANT_NO, Configuration::get(self::PILIPAY_MERCHANT_NO)),
            self::PILIPAY_APP_SECRET => Tools::getValue(self::PILIPAY_APP_SECRET, Configuration::get(self::PILIPAY_APP_SECRET)),
        );
    }

    /**
     * 结账页面, 验证购物车
     * @param $context Context
     */
    public function performValidation($context){
        Pilipay::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));

        $cart = $context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0 || !$this->active){
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

        if (!$authorized){
            die($this->l('This payment method is not available.', 'validation'));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

        // 修改订单状态为待从pilibaba支付
        $this->validateOrder($cart->id, Configuration::get(self::OS_AWAITING), $total,
            $this->displayName, null, null, (int)$currency->id, false, $customer->secure_key);

        // 支付完成后的回调地
        $paidCallbackUrl = $this->context->link->getModuleLink($this->name, 'result', [], true);

        $order = new Order($this->currentOrder);
        if (!Validate::isLoadedObject($order)){
            die($this->l('This order is invalid.', 'pilipay'));
        }

        try {

            // create an order
            $pilipayOrder = new PilipayOrder();
            $pilipayOrder->merchantNO = Configuration::get(self::PILIPAY_MERCHANT_NO);  // a number for a merchant from pilibaba
            $pilipayOrder->appSecret = Configuration::get(self::PILIPAY_APP_SECRET); // the secret key from pilibaba
            $pilipayOrder->currencyType = $this->_getAbbrOfCurrency($currency); // indicates the unit of the following orderAmount, shipper, tax and price
            $pilipayOrder->orderNo = $order->id;
            $pilipayOrder->orderAmount = $total;
            $pilipayOrder->orderTime = date('Y-m-d H:i:s');
            $pilipayOrder->sendTime = date('Y-m-d H:i:s');
            $pilipayOrder->pageUrl = $_SERVER['HTTP_ORIGIN'] . '/index.php?controller=history';
            $pilipayOrder->serverUrl = $paidCallbackUrl;
            $pilipayOrder->shipper = $order->total_shipping;
            $pilipayOrder->tax = $total - $cart->getOrderTotal(false);

            // create a good
            foreach ($order->getProducts() as $product) {
                // 税前价格:
                $price = Product::getPriceStatic((int)$product['id_product'], false,
                    ($product['id_product_attribute'] ? (int)$product['id_product_attribute'] : null),
                    6, null, false, true, $product['cart_quantity'], false,
                    (int)$order->id_customer, (int)$order->id_cart,
                    (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                // 税后价格:
                $price_wt = Product::getPriceStatic((int)$product['id_product'], true,
                    ($product['id_product_attribute'] ? (int)$product['id_product_attribute'] : null),
                    2, null, false, true, $product['cart_quantity'], false,
                    (int)$order->id_customer, (int)$order->id_cart,
                    (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                $product_price = (Product::getTaxCalculationMethod() == PS_TAX_EXC
                    ? Tools::ps_round($price, 2) : $price_wt);

                $productObj = new Product($product['product_id']);
                $productUrl = $context->link->getProductLink($productObj);
                if (!empty($product['image'])){
                    $img = $product['image'];
                    if ($img instanceof Image){
                        $productPictureUrl = $context->link->getImageLink($img->id_image, $img->id_image);
                    }
                }

                $pilipayGood = new PilipayGood();
                $pilipayGood->name = $product['product_name'] . (isset($product['attributes']) ? ' - ' . $product['attributes'] : '');
                $pilipayGood->pictureUrl = $productPictureUrl;
                $pilipayGood->price = $price;
                $pilipayGood->productUrl = $productUrl;
                $pilipayGood->productId = $product['product_id'];
                $pilipayGood->quantity = $product['product_quantity'];
                $pilipayGood->weight = $product['product_weight']; // todo ...
                $pilipayGood->weightUnit = 'kg'; // default kg for presta shop. todo: is there any other unit?
                $pilipayGood->width = $product['product_width'] * 10; // 10: cm -> mm
                $pilipayGood->height = $product['product_height'] * 10;// 10: cm -> mm
                $pilipayGood->length = $product['product_length'] * 10;// 10: cm -> mm

                // add the good to order
                $pilipayOrder->addGood($pilipayGood);

            }

            // render submit form
            echo $pilipayOrder->renderSubmitForm();
            die;
        } catch (PilipayError $e) {
            self::log("Error:" . $e->getMessage());
            die($e->getMessage());
        }
    }

    /**
     * process the pay result
     */
    public function processPayResult(){
        try {
            $payResult = PilipayPayResult::fromRequest();
            if (!$payResult->verify(Configuration::get(self::PILIPAY_APP_SECRET))){
//                die('Invalid request'); // todo..
            }

            $order = new Order($payResult->orderNo);
            if (strcasecmp($order->payment, $this->name) !== 0){
                die('This order is not paid via '.$this->name);
            }

            $orderState = $payResult->isSuccess() ? self::OS_PAID : self::OS_ERROR;
            self::log('info', "order {$order->id} is to be updated to {$orderState} via {$this->name}");

            $orderHistory = new OrderHistory();
            $orderHistory->id_order = $order->id;
            $orderHistory->changeIdOrderState(Configuration::get($orderState), $order);
            $orderHistory->addWithemail();

            self::log('info', "order {$order->id} state updated to " . $orderState);
        } catch (PilipayError $e){
            die($e->getMessage());
        }
    }

    /**
     * @param Currency $currency
     * @return string
     */
    public function _getAbbrOfCurrency($currency){
        switch ($currency->iso_code){
            case 'SGD': return 'USD';
            // todo...
            default:
                throw new Exception("Unsupported currency type: {$currency->iso_code}! Please choose other payment methods.");
        }
    }

    // 后台[设置]页面中: 验证输入内容
    protected function _verifyConfigFromPost()
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue(self::PILIPAY_MERCHANT_NO)){
                $this->_postErrors[] = $this->l('Merchant number is required.');
            } else if (!Tools::getValue(self::PILIPAY_APP_SECRET)){
                $this->_postErrors[] = $this->l('Secret key is required.');
            }
        }
    }

    // 后台[设置]页面中: 保存数据
    protected function _saveConfigFromPost()
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue(self::PILIPAY_MERCHANT_NO, Tools::getValue(self::PILIPAY_MERCHANT_NO));
            Configuration::updateValue(self::PILIPAY_APP_SECRET, Tools::getValue(self::PILIPAY_APP_SECRET));
        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    /**
     * record a log message
     * @param string $level
     * @param string $msg
     */
    public static function log($level, $msg='')
    {
        $msg = date('Y-m-d H:i:s ') . $level . ' '. $msg . PHP_EOL;
        $msg .= sprintf(' -- %s %s with request: %s', $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], json_encode($_REQUEST));

        $e = new Exception();
        $msg .= str_replace('/Users/clarence/work/pilibaba/plugins-for-pilipay/prestashop-dev/prestashop_1.6.1.2/', '', $e->getTraceAsString()) . PHP_EOL;

        file_put_contents('/var/log/prestashop/pilipay.log', $msg, FILE_APPEND);
    }
}

