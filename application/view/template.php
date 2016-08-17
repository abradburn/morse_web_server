<?php
if(!defined('BASEPATH')){exit('No direct script access allowed.');}
	// 35 lines.
	function index($data = NULL){
		if(AJAX){
			if($data){

			}
			else{

			}
		}
		else{
			$include = array(
				'title' => '',
				'file' => array(
					  /*
					array(
						'type' => 'javascript',
						'url' => 'URL'
					),
					array(
						'type' => 'css',
						'url' => 'URL'
					)
					  */
				)
			);
			xhtmlHeader($include);
			if($data){

			}
			else{

			}
			xhtmlFooter();
		}
	}
?>
