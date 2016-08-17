<?php
if(!defined('BASEPATH')){exit('No direct script access allowed.');}
  // Class names must be in all lower case to be routed correctly.
class _split_file extends _controller{

/*
	Available resources provided by the parent class include:

	$this->load;

	$this->input;

	$this->_load_view();

	$this->_load_helper();
*/

	public function __construct(){
		parent::__construct();
	}

	public function __destruct(){

	}

	public function _split_file_csv($in_file_path = NULL, $split_path = NULL, $file_name_prefix = NULL, $file_size = 1000){
		if(!is_readable($in_file_path)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}
		
		if(!is_readable($split_path)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		if($file_size > 10000){
			$file_size = 10000;
		}elseif($file_size < 1000){
			$file_size = 1000;
		}

		if(!$in_file_handle = fopen($in_file_path, 'r')){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		  // Here we assume that every parent file will include a first line that is the column headers.
		$column_headers = fgets($in_file_handle);

		$file_int = 1;
		$out_file_handle;
		$line_int = 0;

		while(!feof($in_file_handle)){
			$out_file_path = $split_path.$file_name_prefix.$file_int.'.csv';
			if(!$out_file_handle = fopen($out_file_path, 'w+')){
				trigger_error('', E_USER_NOTICE);
				return FALSE;
			}
			fwrite($out_file_handle, $column_headers);
			$file_int++;

			for($line_int = 0; $line_int < $file_size; $line_int++){
				if(!$line = fgets($in_file_handle)){
					$line_int = $file_size;
					continue;
				}
				fwrite($out_file_handle, $line);
			}

			fclose($out_file_handle);
		}

		return TRUE;
	}

/*
	public function X(){
		$data = array(
		);

		$this->_load_view('');
		_function_name($data);
	}
*/
}
?>
