<?php
if(!defined('BASEPATH')){exit('No direct script access allowed.');}
class _mysql_functions{
	protected $connection = FALSE;

	public function __construct(){

	}

	public function __destruct(){

	}

	public function _connect(&$database = NULL, $mode = NULL){
		if(!isset($database) || !isset($mode)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		if(!$database->_connect($this->connection, $mode)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		return TRUE;
	}

	public function _query(&$destination = NULL, $query = NULL, $simplify = TRUE){

		if(!is_null($destination) || !isset($query)){
			trigger_error('Either the referenced destination is not empty, or there was no query provided.', E_USER_NOTICE);
			return FALSE;
		}

		$return = NULL;
		
		if($result = $this->connection -> multi_query($query)){
			$set_count = 0;
			do{
				$set = $this->connection -> store_result();

				if(!$set){
					if($error = $this->connection -> error){
						$destination = $error;
						return FALSE;
					}
					continue;
				}

				while($row = $set -> fetch_assoc()){
				  	  // Parse array of values for each row.
					$return[$set_count][] = $row;
				}

				$set -> free();
				$set_count++;
			}while($this->next_set());
			
		}else{
			$destination = $this->connection -> error;
			return FALSE;
		}

		  // Simplified result for situations where there was only one query that returned data.
		if($simplify){
			$destination = $this->simplify_result($return);
		}else{
			if($set_count == 1 && isset($return[0])){
				$destination = $return[0];
			}else{
				$destination = $return;
			}
		}
		return TRUE;
	}

	private function next_set(){
		if(!$this->connection -> more_results()){
			return FALSE;
		}

		$this->connection -> next_result();
		return TRUE;
	}

	private function simplify_result($return){
		if(is_array($return) && count($return) === 1){
			return $this->simplify_result(current($return));
		}
		return $return;
	}

	public function _mysqliEscape($value){
		return mysqli_real_escape_string($this->connection, $value);
	}
}
?>
