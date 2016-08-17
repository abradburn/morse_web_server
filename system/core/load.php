<?php

if(!defined('BASEPATH')){exit('No direct script access allowed.');}

class _load{
	protected $class;

	protected $model;

	public function __construct(){
		$this->model['init'] = FALSE;
	}

	public function __destruct(){

	}

	public function _load_class(&$destination = NULL, $name = NULL, $parameters = NULL){
		if(!is_null($destination) || is_null($name)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$class_key = strtolower($name);
		$secure_name = '_'.$name;
		  unset($name);

		  // Start by checking if the requested class has been initialized previously, and cached in the "$class" array.
		if(isset($this->class[$class_key])){
			$destination = $this->class[$class_key];
			return TRUE;
		}

		  // Check to see if the class exists somewhere else in this instance, if not, load the file.
		if(!class_exists($secure_name)){
			$path = SYSPATH.'core/'.$class_key.'.php';

			if(!is_readable($path)){
				trigger_error("BIKOGF", E_USER_NOTICE);
				return FALSE;
			}

			require_once $path;
			unset($path);
		}

		if(!class_exists($secure_name)){
			trigger_error("XXFWQI", E_USER_NOTICE);
			return FALSE;
		}

		$this->class[$class_key] = new $secure_name($parameters);
		$destination = $this->class[$class_key];

		return TRUE;
	}

	public function _load_mysql(&$destination = NULL, $database = NULL, $mode = NULL){
		if(!is_null($destination) || is_null($database) || is_null($mode)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$mode = strtolower($mode);
		$database_key = strtolower($database);
		  unset($database);
		$mode_key = $database_key.'_'.$mode;

		$protected_database = '_'.$database_key;

		  // Check to see if the database and mode have been initialized and cached in the "$model" array, if so, return the existing instance.
		if(isset($this->model[$mode_key])){
			$destination = $this->model[$mode_key];
			return TRUE;
		}

		if(isset($this->model[$database_key])){
			$this->model[$mode_key] = new _mysql_functions();
			if(!$this->model[$mode_key]->_connect($this->model[$database_key], $mode)){
				trigger_error('', E_USER_NOTICE);
				return FALSE;
			}
			$destination = $this->model[$mode_key];
			return TRUE;
		}

		if(!class_exists('_mysql_functions')){
			$path = SYSPATH.'core/mysql_functions.php';
			if(!is_readable($path)){
				trigger_error("AKREDR", E_USER_NOTICE);
				return FALSE;
			}
			require_once $path;
			unset($path);
		}

		if(!class_exists($protected_database)){
			$path = APPPATH.'config/'.$database_key.'.php';
			if(!is_readable($path)){
				trigger_error('', E_USER_NOTICE);
				return FALSE;
			}
			require_once $path;
			unset($path);
		}

		$this->model[$database_key] = new $protected_database();

		$this->model[$mode_key] = new _mysql_functions();
		$this->model[$mode_key]->_connect($this->model[$database_key], $mode);

		$destination = $this->model[$mode_key];

		return TRUE;
	}

	public function _load_psql(&$destination = NULL, $database = NULL, $mode = NULL){
		if(!is_null($destination) || is_null($database) || is_null($mode)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$mode = strtolower($mode);
		$database_key = strtolower($database);
		  unset($database);
		$mode_key = $database_key.'_'.$mode;

		$protected_database = '_'.$database_key;

		  // Check to see if the database and mode have been initialized and cached in the "$model" array, if so, return the existing instance.
		if(isset($this->model[$mode_key])){
			$destination = $this->model[$mode_key];
			return TRUE;
		}

		if(isset($this->model[$database_key])){
			$this->model[$mode_key] = new _psql_functions();
			if(!$this->model[$mode_key]->_connect($this->model[$database_key], $mode)){
				trigger_error('', E_USER_NOTICE);
				return FALSE;
			}
			$destination = $this->model[$mode_key];
			return TRUE;
		}

		if(!class_exists('_psql_functions')){
			$path = SYSPATH.'core/psql_functions.php';
			if(!is_readable($path)){
				trigger_error("AKREDR", E_USER_NOTICE);
				return FALSE;
			}
			require_once $path;
			unset($path);
		}

		if(!class_exists($protected_database)){
			$path = APPPATH.'config/'.$database_key.'.php';
			if(!is_readable($path)){
				trigger_error('', E_USER_NOTICE);
				return FALSE;
			}
			require_once $path;
			unset($path);
		}


		$this->model[$database_key] = new $protected_database();

		$this->model[$mode_key] = new _psql_functions();
		$this->model[$mode_key]->_connect($this->model[$database_key], $mode);

		$destination = $this->model[$mode_key];

		return TRUE;
	}
}
?>
