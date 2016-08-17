<?php
if(!defined('BASEPATH')){exit('No direct script access allowed.');}
class _input{
	protected $security;

	protected $input;

	public function __construct(){
		if(!$GLOBALS['_LOAD']->_load_class($this->security, 'security')){
			trigger_error('', E_USER_ERROR);
		}

		if(isset($GLOBALS['argv'])){
			define('CLINPUT', TRUE);
			parse_str(implode('&', array_slice($GLOBALS['argv'], 1)), $_GET);
			$GLOBALS['argv'] = array();
		}else{
			define('CLINPUT', FALSE);
		}

		$this->input = array_change_key_case(array_merge($_POST, $_GET, $_COOKIE), CASE_LOWER);

		if(array_key_exists('ajax', $this->input)){
			define('AJAX', TRUE);
		}else{
			define('AJAX', FALSE);
		}
		$_POST = array();
		$_GET = array();
		$_COOKIE = array();
	}

	public function __destruct(){

	}

	public function _insert($input){
		if(!is_array($input)){
			trigger_error('X', E_USER_NOTICE);
			return NULL;
		}

		foreach($input as $i_key => $i_value){
			$this->input[$i_key] = $i_value;
		}

		return TRUE;
	}

	  // The basic premise of this class is to isolate all user input from direct access by subsequent scripts.
	  // The "_secured_input" method should enable a use to identify a specific value provided by the user, and a data type.
	  // Once the method has those parameters, it will seek the value in the request array, and return it type cast as the specified data type.

	  // This class can also serve to verify that a variable is set.  In the case that it is not, NULL will be returned.

	public function _secured_input(&$destination, $request = NULL, &$input = NULL){
		  // Request will be provided as an array of keys which coincide with keys in the "input" array.  The value will be another array or a value which represents the data type requested.
		if(!isset($request) || !is_array($request)){
			trigger_error('XXFWQI', E_USER_NOTICE);
			  // This may be better off if it returns NULL.
			return FALSE;
		}

		if(!isset($input)){
			$input =& $this->input;
		}

		$cleaned = NULL;

		  // Every request will be an array with an optional array, which will finally end in a value.
		  // Here we can use recursion.
		foreach($request as $r_key => $r_value){
			if(!array_key_exists($r_key, $input)){
				//trigger_error('MHPOOR', E_USER_NOTICE);
				return FALSE;
			}

			if(is_array($r_value) && is_array($input[$r_key])){
				$cleaned[$r_key] = $this->_secured_input($r_value, $input[$r_key]);
			}elseif(is_array($r_value) && !is_array($input[$r_key])){
				  // There has been a misunderstanding of how the input data is structured.
				trigger_error('BMDZHH', E_USER_NOTICE);
				return FALSE;
			}elseif(!is_array($r_value) && is_array($input[$r_key])){
				foreach($input[$r_key] as $i_key => $i_value){
					if(is_array($i_value)){
						  // There has been a misunderstanding of how the input data is structured.
						trigger_error('XBHQGF', E_USER_NOTICE);
						return FALSE;
					}else{
						$cleaned[$r_key][$i_key] = $this->security -> _secured($i_value, $r_value);
					}
				}
			}elseif(!is_array($r_value) && !is_array($input[$r_key])){
				$cleaned[$r_key] = $this->security -> _secured($input[$r_key], $r_value);
			}else{
				trigger_error('QOYXAF', E_USER_NOTICE);
				return FALSE;
			}
		}

		$destination = $this->simplify_result($cleaned);

		return TRUE;
	}

	private function simplify_result($cleaned){
		if(is_array($cleaned) && count($cleaned) === 1){
			return $this->simplify_result(current($cleaned));
		}
		return $cleaned;
	}
}
?>
