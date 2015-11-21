<?php

class Pilipay extends Module{
    public function __construct(){
        $this->name = 'pilipay';
        parent::__construct();

        $this->tab = 'payments_gateways';
        $this->version = '1.0';
        $this->author = 'Pilibaba';
        $this->displayName = $this->l('Pilipay');
        $this->description = $this->l('Pilibaba payment gateway -- accept China Yuan and deal international shipping.');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');

        self::log(print_r([
            'class of this' => get_class($this),
            'parent classes' => self::getParentClasses($this),
            'currency' => class_exists('Currency')
        ], true));
    }

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

        $msg = date('Y-m-d H:i:s ').$msg.PHP_EOL;

        $e = new Exception();
        $msg .= $e->getTraceAsString() . PHP_EOL;

        file_put_contents('/var/log/prestashop/pilipay.log', $msg, FILE_APPEND);
    }
}

