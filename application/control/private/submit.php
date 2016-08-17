<?php
if(!defined('BASEPATH')){exit('No direct script access allowed.');}
  // Class names must be in all lower case to be routed correctly.
class submit extends _controller{

/*
	Available resources provided by the parent class include:

	$this->load;

	$this->input;

	$this->load_view();

	$this->load_helper();
*/

	private $morseduino;

	public function __construct(){
		parent::__construct();

		if(!$this->load -> _load_mysql($this->morseduino, 'morseduino', 'write')){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}
	}

	public function __destruct(){

	}

	public function index(){

	}

	public function cleanup(){
		$query = "SELECT `submission_id` FROM `submissions`;";

		if(!$this->morseduino -> _query($submission_id, $query)){
			trigger_error($submission_id, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		foreach($submission_id AS $sid_key => $sid_value){
			$query = "SELECT `content` FROM `submissions` WHERE `submission_id` = ".(int) $sid_value['submission_id'].";";

			if(!$this->morseduino -> _query($content, $query)){
				trigger_error($content, E_USER_NOTICE);
				return FALSE;
			}
			unset($query);

			if(!$this->_validate_morse($content)){
				trigger_error('', E_USER_NOTICE);
				return FALSE;
			}
echo $content."\n";

			$query = "UPDATE `submissions` SET `content` = \"".$content."\" WHERE `submission_id` = ".(int) $sid_value['submission_id'].";";
			unset($content);

			if(!$this->morseduino -> _query($result, $query)){
				trigger_error($result, E_USER_NOTICE);
				return FALSE;
			}
			unset($query);
			unset($result);

			echo $sid_value['submission_id']." Done\n";
		}
	}

	public function import(){
		$dir = '/var/www/morse_project/application/upload/';
		$file = 'chapter_one.txt';

		$input = file_get_contents($dir.$file);

		if(!$this->_validate_morse($input)){
			echo "Submission validation failed.\n";
		}else{
			echo "Submission validation succeeded.\n";
		}

		$query = "INSERT INTO `submissions` (`content`) VALUES (\"".$input."\");";

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);
		unset($result);
	}

	private function _validate_morse(&$text = NULL){
		if(is_null($text)){
			return FALSE;
		}

		$text = strtoupper($text);

		$text = preg_replace('/[\r\n]/', ' ', $text);

		$text = preg_replace('/[ ]{2,}/', ' ', $text);

		$text = preg_replace('/[^A-Za-z0-9,\.@\?\/ ]/', '', $text);

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
