<?php

// todo: PHP 兼容性检查 -- 至少兼任5.2

if (!defined('_PS_VERSION_'))
    exit;

class Pilipay extends PaymentModule{
    protected $_html = '';
    protected $_postErrors = array();

    public $merchantNo;
    public $appSecret;

    const PILIPAY_MERCHANT_NO = 'PILIPAY_MERCHANT_NO';
    const PILIPAY_APP_SECRET  = 'PILIPAY_APP_SECRET';

    public function __construct(){
        $this->name = 'pilipay';

        $this->tab = 'payments_gateways';
        $this->version = '1.0';
        $this->author = 'Pilibaba';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 1; // todo what is this?

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $config = Configuration::getMultiple(array(self::PILIPAY_MERCHANT_NO, self::PILIPAY_APP_SECRET));
        if (!empty($config[self::PILIPAY_MERCHANT_NO])){
            $this->merchantNo = $config[self::PILIPAY_MERCHANT_NO];
        }
        if (!empty($config[self::PILIPAY_APP_SECRET])){
            $this->appSecret = $config[self::PILIPAY_APP_SECRET];
        }

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Pilipay');
        $this->description = $this->l('Pilibaba payment gateway -- accept China Yuan and deal international shipping.');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');
        if (empty($this->merchantNo) || empty($this->appSecret)){
            $this->warning = $this->l('Merchant number and secret key must be configured before using this module.');
        }

        if (!count(Currency::checkPaymentCurrencies($this->id))){
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    // 安装模块, 注册各种钩子
    public function install()
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('displayPaymentEU') || !$this->registerHook('paymentReturn'))
            return false;
        return true;
    }

    // 卸载,清空配置
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
            $this->_postValidation();
            if (!count($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors as $err)
                    $this->_html .= $this->displayError($err);
        } else{ // GET request:
            $this->_html .= '<br />';
        }

        $this->_html .= $this->_displayInfo();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    // [前台]下单时选择支付的方式页面
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
        return $this->display(__FILE__, 'payment.tpl');
    }

    // todo: ???
    public function hookDisplayPaymentEU($params)
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        if (!$this->active)
            return;

        if (!$this->checkCurrency($params['cart']))
            return;

        $payment_options = array(
            'cta_text' => $this->l('Pay by Pilibaba'),
            'logo' => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/powered-by-pilibaba.jpg'),
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
        );

        return $payment_options;
    }

    // todo: ???
    public function hookPaymentReturn($params)
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        if (!$this->active)
            return;

        $state = $params['objOrder']->getCurrentState();
        // todo...
        if (in_array($state, array(Configuration::get('PS_OS_BANKWIRE'), Configuration::get('PS_OS_OUTOFSTOCK'), Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')))) {
            $this->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
                'pilipayDetails' => Tools::nl2br($this->details),
                'pilipayAddress' => Tools::nl2br($this->address),
                'pilipayOwner' => $this->owner,
                'status' => 'ok',
                'id_order' => $params['objOrder']->id
            ));
            if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
                $this->smarty->assign('reference', $params['objOrder']->reference);
        } else
            $this->smarty->assign('status', 'failed');
        return $this->display(__FILE__, 'payment_return.tpl');
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
    public function renderForm()
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
                        'type' => 'text',
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

    // 后台[设置]页面中: 验证输入内容
    protected function _postValidation()
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue(self::PILIPAY_MERCHANT_NO))
                $this->_postErrors[] = $this->l('Merchant number is required.');
            elseif (!Tools::getValue(self::PILIPAY_APP_SECRET))
                $this->_postErrors[] = $this->l('Secret key is required.');
        }
    }

    // 后台[设置]页面中: 保存数据
    protected function _postProcess()
    {
        self::log(sprintf("Calling %s with %s", __METHOD__, json_encode(func_get_args())));
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue(self::PILIPAY_MERCHANT_NO, Tools::getValue(self::PILIPAY_MERCHANT_NO));
            Configuration::updateValue(self::PILIPAY_APP_SECRET, Tools::getValue(self::PILIPAY_APP_SECRET));
        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    // 后台[设置]页面中: 显示警告信息
    protected function _displayInfo()
    {
        return $this->display(__FILE__, 'infos.tpl'); // 警告内容
    }
    //////////// debug functions /////////////
    public static function getParentClasses($obj){
        $class = new ReflectionClass($obj);
        $parents = [ ];

        while ($class && !in_array($class->getName(), $parents)){
            $parents[] = $class->getName();
            $class = $class->getParentClass();
        }

        return $parents;
    }

    public static function log($msg, $args=array()){
        if (!empty($args)){
            $msg = strtr($msg, $args);
        }

        $msg = date('Y-m-d H:i:s ') . $msg . PHP_EOL;
        $msg .= sprintf(' -- %s %s with request: %s', $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], json_encode($_REQUEST));

        $e = new Exception();
        $msg .= str_replace('/Users/clarence/work/pilibaba/plugins-for-pilipay/prestashop-dev/prestashop_1.6.1.2/', '', $e->getTraceAsString()) . PHP_EOL;

        file_put_contents('/var/log/prestashop/pilipay.log', $msg, FILE_APPEND);
    }
}

