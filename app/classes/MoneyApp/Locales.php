<?php

namespace MoneyApp;

use AmiLabs\DevKit\Application;

class Locales {
    protected static $oInstance = FALSE;
    protected $aLocales = array();
    protected $lng;

    public static function getInstance($lng = 'ru'){
        if(FALSE === Locales::$oInstance){
            Locales::$oInstance = new self($lng);
        }
        return Locales::$oInstance;
    }

    protected function __construct($lng){
        $this->lng = $lng;
        if(!isset($this->aLocales[$this->lng])){
            $cfgPath = Application::getInstance()->getConfig()->get("path/cfg");
            $filename = $cfgPath . '/' . 'locales.' . $this->lng . '.php';
            if(file_exists($filename)){
                $this->aLocales[$this->lng] = require_once $filename;
            }else{
                throw new \Exception('Cannot load locales file for locale "' . $this->lng . '"');
            }
        }
    }

    public function get($name){
        $aLocales = $this->aLocales[$this->lng];
        $aParts = explode('.', $name);
        $found = TRUE;
        foreach($aParts as $key){
            if(isset($aLocales[$key])){
                $aLocales = $aLocales[$key];
            }else{
                $found = FALSE;
                break;
            }
        }
        return $found ? $aLocales : '';
    }
}