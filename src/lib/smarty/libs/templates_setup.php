<?php

// Smarty Library Dateien laden
require(LIB.'smarty/libs/Smarty.class.php');

// ein guter Platz um Applikations spezifische Libraries zu laden
// require('guestbook/guestbook.lib.php');

class Template extends Smarty {

    var $choosed = 'old/';

    private static $instance;
	
    private function  __construct() {
  

        $this->Smarty();

        $this->template_dir = TEMPLATESYSTEM.'templates/'.$this->choosed;
        $this->compile_dir = TEMPLATESYSTEM.'templates_c/';
        $this->config_dir = TEMPLATESYSTEM.'templates/'.$this->choosed;
        $this->cache_dir = TEMPLATESYSTEM.'cache/';
        $this->debug_tpl = SMARTY_DIR.'debug.tpl';
        $this->debugging = false;
        $this->security = false;
        $this->secure_dir[] = PROJECT_DIR."lib/";
        
        $this->caching = false;
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setTemplateSet($tpl_set) {

        $this->template_dir = TEMPLATESYSTEM.'templates/' . $tpl_set.'/';
        $this->choosed = $tpl_set.'/';
		 $this->compile_dir = TEMPLATESYSTEM.'templates_c_' . $tpl_set.'/';
        return $this;
    }

}
?> 