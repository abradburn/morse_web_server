<?php
if(!defined('BASEPATH')){exit('No direct script access allowed.');}
class _psql_functions{
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
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$return = NULL;
		
		if($result = pg_query($this->connection, $query)){
			if(!$result){
				if($error = $this->connection -> error){
					$destination = $error;
					return FALSE;
				}
			}

			while($row = pg_fetch_assoc($result)){
			  	  // Parse array of values for each row.
				$return[] = $row;
			}
			
		}else{
			$destination = $this->connection -> error;
			return FALSE;
		}
		  // Simplified result for situations where there was only one query that returned data.
		if($simplify){
			$destination = $this->simplify_result($return);
		}else{
			$destination = $return;
		}
		return TRUE;
	}

	private function simplify_result($return){
		if(is_array($return) && count($return) === 1){
			return $this->simplify_result(current($return));
		}
		return $return;
	}

	public function _return_result(&$destination = NULL, $query = NULL){
		if(!is_null($destination) || !isset($query)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		if($result = pg_query($this->connection, $query)){
			if(!$result){
				if($error = $this->connection -> error){
					$destination = $error;
					return FALSE;
				}
			}

			$destination = $result;
		}else{
			$destination = $this->connection -> error;
			return FALSE;
		}
		return TRUE;
	}

	
}
?>
