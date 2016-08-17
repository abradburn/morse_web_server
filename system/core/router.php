<?php
if(!defined('BASEPATH')){exit('No direct script access allowed.');}
class _router{

	protected $load;

	protected $input;

	protected $security;

	protected $default = 'index';

	public function __construct(){
		$this->load = $GLOBALS['_LOAD'];

		if(!$this->load -> _load_class($this->input, 'input')){
			trigger_error('', E_USER_ERROR);
		}

		if(!$this->load -> _load_class($this->security, 'security')){
			trigger_error('', E_USER_ERROR);
		}

		  // In the past, authentication was included here, I am going to skip that for now, and revisit it later.
	}

	public function __destruct(){

	}

	  // The purpose of the "_validate_class" method is to ensure that the requested class is not protected, and that the requesting user is authorized.

	  // For the time being, I am going to omit the authentication portion of the code.
	protected function validate_class($class){
		$public_path = APPPATH.'control/public/'.$class.'.php';
		$private_path = APPPATH.'control/private/'.$class.'.php';

		if(is_readable($public_path)){
			require_once $public_path;
		}elseif(is_readable($private_path)){
			  // Here we will authenticate the requesting user.
			require_once $private_path;
		}else{
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		if(!class_exists($class)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		return TRUE;
	}

	protected function validate_method($class, $method){
		if(!method_exists($class, $method)){
			trigger_error('No method found in the specified class.', E_USER_NOTICE);
			return FALSE;
		}

		return TRUE;
	}

	public function _launch($class = NULL, $method = NULL){
		if(is_null($class)){
			if(!$this->input -> _secured_input($class, array('class' => 'string'))){
				trigger_error('', E_USER_NOTICE);
			}
			$class = $this->check_protected($class);
		}

		$class = strtolower($class);

		if(is_null($method)){
			if(!$this->input -> _secured_input($method, array('method' => 'string'))){
				trigger_error('No method was specified in the user provided input.', E_USER_NOTICE);
			}
			$method = $this->check_protected($method);
		}

		$method = strtolower($method);

		if(!$this->validate_class($class)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		if(!$this->validate_method($class, $method)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$go = new $class();
		unset($class);

		$go->$method();
		return TRUE;
	}

	protected function check_protected($string){
		if(!$string){
			return $this->default;
		}

		$string = $this->security->_remove_invisible_characters($string);
		if(preg_match('/^_/', $string)){
			trigger_error('', E_USER_NOTICE);
			return $this->default;
		}else{
			return $string;
		}
	}
	
}

?>
