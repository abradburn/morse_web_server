<?php
if(!defined('BASEPATH')){exit('No direct script access allowed.');}
  // Class names must be in all lower case to be routed correctly.
class _gchart extends _controller{

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

	public function _format_data(&$destination, &$structure = NULL, &$data = NULL){
		if(is_null($structure) || is_null($data)){
			return FALSE;
		}

		$destination = array();

		foreach($data as $r_key => $row){
			$this_row = array();

			foreach($structure as $key => $column){
				$type =& $column['data_type'];

				if($type == 'string'){
					$this_row[$key] =  $row[$column['field_name']];
				}elseif($type == 'number'){
					if(isset($column['formatting'])){
						$formatting = $column['formatting'];
						if($formatting == 'decimal'){
							$this_row[$key] = (float) $row[$column['field_name']];
						}
					}else{
						$this_row[$key] = (int) $row[$column['field_name']];
					}
				}elseif($type == 'boolean'){

				}elseif($type == 'date'){
					$temp = new DateTime($row[$column['field_name']], new DateTimeZone('America/New_York'));
					$this_row[$key] = $temp -> format('Y-m-d').' 00:00:00';
				}elseif($type == 'datetime'){

				}elseif($type == 'timeofday'){
					$temp = new DateTime($row[$column['field_name']], new DateTimeZone('America/New_York'));
					$this_row[$key] = array(
						(int) $temp -> format('H'),
						(int) $temp -> format('i'),
						(int) $temp -> format('s'),
						0
					);
					unset($temp);
				}else{

				}
			}

			array_push($destination, $this_row);

			unset($data[$r_key]);
		}

		return FALSE;
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
