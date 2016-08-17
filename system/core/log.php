<?php

if(!defined('BASEPATH')){exit('No direct script access allowed.');}

class _log{

	  /* The design of this class is to handle all notifications to the user.
		Critical Errors will terminate the script with a basic HTML display.
		Notices to the user about errors in input, etc.
	  */

	private $log_buffer;

	private $log_file_handle;

	private $severe;

	private $is_severe;

	public function __construct(){
		register_shutdown_function(array($this, '_fatal'));
		set_error_handler(array($this, '_log_error'));
		set_exception_handler(array($this, '_log_exception'));
		ini_set('display_errors', 'off');
		error_reporting(E_ALL);

		$this->log_buffer = array();

		$log_file_path = APPPATH.'log/error_log.txt';
		if(!$this->log_file_handle = fopen($log_file_path, 'a')){
			$this->log_file_handle = FALSE;
		}

		$this->severe = array(
			1 => 'E_ERROR',
			2 => 'E_WARNING',
			4 => 'E_PARSE',
			8 => 'E_NOTICE',
			16 => 'E_CORE_ERROR',
			32 => 'E_CORE_WARNING',
			64 => 'E_COMPILE_ERROR',
			128 => 'E_COMPILE_WARNING',
			256 => 'E_USER_ERROR',
			512 => 'E_USER_WARNING',
			//1024 => 'E_USER_NOTICE',
			2048 => 'E_STRICT',
			4096 => 'E_RECOVERABLE_ERROR',
			8191 => 'E_ALL'
		);

		$this->is_severe = FALSE;
	}

	public function __destruct(){
		if($this->is_severe){
			$this->_email_log();
			//var_dump($this->log_buffer);
		}
	}

	/*
		Static methods which enable this log class to be used before it is properly initiated.
	*/

	public static function _static_fatal(){
		if(!is_null($error = error_get_last())){
			self::_static_error($error['type'], $error['message'], $error['file'], $error['line']);
		}else{

		}
	}

	public static function _static_error($error_level, $error_message, $error_file, $error_line, $error_context = NULL){
		self::_static_exception(new ErrorException($error_message, 0, $error_level, $error_file, $error_line));
	}

	public static function _static_exception($exception){
		//echo "Error: {$exception->getMessage()}, {$exception->getFile()}, {$exception->getLine()}\n";
		//exit();
	}

	/*
		Non-static methods.
	*/

		// The shutdown function is called at the end of every script.
	public function _fatal(){
		if(!is_null($error = error_get_last())){
			$this->_log_error($error['type'], $error['message'], $error['file'], $error['line']);
		}else{

		}
	}

	public function _log_error($error_level, $error_message, $error_file, $error_line, $error_context = NULL){
		$this->_log_exception(new ErrorException($error_message, $error_level, $error_level, $error_file, $error_line));
	}

	public function _log_exception($exception){
		$date_time = new DateTime('now', new DateTimeZone('America/New_York'));

		if(isset($this->severe[$exception->getCode()])){
			$this->is_severe = TRUE;
		}

		if(isset($this->severe[$exception->getCode()])){
			$this_exception = "\"".$date_time -> format('Y-m-d H:i:s')."\", \"{$exception->getCode()}\", \"{$this->severe[$exception->getCode()]}\", \"{$exception->getFile()}\", \"{$exception->getLine()}\", \"{$exception->getMessage()}\"";
		}else{
			$this_exception = "\"".$date_time -> format('Y-m-d H:i:s')."\", \"{$exception->getCode()}\", \"N\A\", \"{$exception->getFile()}\", \"{$exception->getLine()}\", \"{$exception->getMessage()}\"";
		}

		
		array_push($this->log_buffer, $this_exception);

		if($this->log_file_handle){
			fwrite($this->log_file_handle, $this_exception."\n");
		}
	}

	/*

	*/

	private function _email_log(){
		$this_email = array(
			'to' => ADMIN_EMAIL,
			'subject' => 'ERROR LOG',
			'headers' => 'MIME-Version: 1.0\r\nContent-type: text/html; charset=iso-8859-1\r\n',
			'message' => implode("\r\n", $this->log_buffer)
		);

		mail($this_email['to'], $this_email['subject'], $this_email['message'], $this_email['headers']);
	}
}
?>
