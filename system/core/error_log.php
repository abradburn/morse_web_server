<?php
if(!defined('BASEPATH')){exit('No direct script access allowed.');}
class _error_log{

	  /* The design of this class is to handle all notifications to the user.
		Critical Errors will terminate the script with a basic HTML display.
		Notices to the user about errors in input, etc.
	  */

	private $log_file_handle;

	private $date_time;

	public function __construct(){
		register_shutdown_function(array($this, '_fatal'));
		set_error_handler(array($this, '_log_error'));
		set_exception_handler(array($this, '_log_exception'));
		ini_set('display_errors', 'off');
		error_reporting(E_ALL);

		$log_file_path = APPPATH.'log/error_log.txt';
		if(!is_writable($log_file_path)){
			$this->log_file_handle = FALSE;
		}else{
			if(!$this->log_file_handle = fopen($log_file_path, 'a')){
			//if(!$this->log_file_handle = fopen($log_file_path, 'w')){
				$this->log_file_handle = FALSE;
			}
		}
	}

	public function __destruct(){

	}

	public static function _static_error($error_level, $error_message, $error_file, $error_line, $error_context = NULL){
		self::_static_exception(new ErrorException($error_message, 0, $error_level, $error_file, $error_line));
	}

	public static function _static_exception($exception){
		echo "Error: {$exception->getMessage()}, {$exception->getFile()}, {$exception->getLine()}\n";
		exit();
	}

	public static function _static_fatal(){
		$error = error_get_last();
		if($error['type'] == 'E_ERROR'){
			self::_static_error($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}

	public function _log_error($error_level, $error_message, $error_file, $error_line, $error_context = NULL){
		$this->_log_exception(new ErrorException($error_message, 0, $error_level, $error_file, $error_line));
	}

	public function _log_exception($exception){
		$this->date_time = new DateTime('now', new DateTimeZone('America/New_York'));

		if($this->log_file_handle){
			fwrite($this->log_file_handle, "{$this->date_time -> format('Y-m-d H:i:s')} Error: {$exception->getMessage()}, {$exception->getFile()}, {$exception->getLine()}\n");
		}else{
			echo "Error: {$exception->getMessage()}, {$exception->getFile()}, {$exception->getLine()}\n";
		}
	}

	public function _fatal(){
		$error = error_get_last();
		if($error['type'] == 'E_ERROR'){
			$this->_log_error($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}
}
?>
