<?php

if(!defined('BASEPATH')){exit('No direct script access allowed.');}

class _controller{

	protected $load;
	protected $input;
	protected $security;
	protected $log;

	public function __construct(){
		$this->load = &$GLOBALS['_LOAD'];

		if(!$this->load -> _load_class($this->input, 'input')){
			trigger_error('', E_USER_ERROR);
		}

		if(!$this->load -> _load_class($this->security, 'security')){
			trigger_error('', E_USER_ERROR);
		}

		if(!$this->load -> _load_class($this->log, 'security')){
			trigger_error('', E_USER_ERROR);
		}

		$this->_load_view('header');
		$this->_load_view('footer');
	}

	public function __destruct(){

	}

	public function _init_dep(){

	}

	protected function _load_view($view = NULL){
		if(!isset($view)){
			trigger_error('X', E_USER_NOTICE);
			return FALSE;
		}

		$path = APPPATH.'view/'.$view.'.php';

		if(!is_readable($path)){
			trigger_error('X', E_USER_NOTICE);
			return FALSE;
		}

		require_once $path;

		unset($path);
		return TRUE;
	}

	protected function _load_helper($helper = NULL){
		if(!isset($helper)){
			trigger_error('Helper has not been defined.', E_USER_NOTICE);
			return FALSE;
		}

		$path = SYSPATH.'helper/'.$helper.'.php';

		if(!is_readable($path)){
			trigger_error('Path for helper is unreadable.', E_USER_NOTICE);
			return FALSE;
		}

		require_once $path;
		unset($path);
		return TRUE;
	}
}
?>
