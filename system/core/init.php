<?php
if(!defined('BASEPATH')){exit('No direct script access allowed.');}

  // Load variables and functions common to all requests.
$_load = SYSPATH.'core/load.php';
if(!is_readable($_load)){
	trigger_error('EOQRCO', E_USER_ERROR);
}
require_once $_load;
unset($_load);

$_LOAD = new _load();

  // Load alternative error handling mechanics.
if(!$_LOAD->_load_class($_LOG, 'log')){
	trigger_error('', E_USER_ERROR);
}

  // Load additional user configuration settings.
$_config = APPPATH.'config/config.php';
if(!is_readable($_config)){
	trigger_error('EJIYMH', E_USER_ERROR);
}
require_once $_config;
unset($_config);

  // Load input class.
if(!$_LOAD->_load_class($_INPUT, 'input')){
	trigger_error('', E_USER_ERROR);
}

  // Load controller.
$_controller = SYSPATH.'core/controller.php';
if(!is_readable($_controller)){
	trigger_error('', E_USER_ERROR);
}
require_once $_controller;
unset($controller);

if(!$_LOAD->_load_class($_ROUTER, 'router')){
	trigger_error('', E_USER_ERROR);
}

  // Launch the requested class & method.
if(!$_ROUTER->_launch()){
	  // In the case that the requested class & method cannot be loaded, redirect to the default of index.
	$_ROUTER->_launch('index', 'index');
}
?>
