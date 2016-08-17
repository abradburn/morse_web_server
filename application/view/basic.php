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

	function _table($data = NULL){
		if(AJAX){
			if($data){
				echo "<table>\n";
				if(!is_null($data['columns'])){
					echo "<tr class=\"header\">\n";
					foreach($data['columns'] AS $cKey => $cValue){
						echo '<td>'.$cValue['display_name'].'</td>'."\n";
					}
					echo "</tr>\n";
				}
				foreach($data['data'] as $dKey => $dValue){
					echo "<tr>\n";
					foreach($dValue as $vKey => $vValue){
						echo '<td class="'.$data['columns'][$vKey]['class'].'">'.$vValue.'</td>'."\n";
					}
					echo "</tr>\n";
				}
				echo "</table>\n";
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
				echo "<table>\n";
				if(!is_null($data['columns'])){
					echo "<tr>\n";
					foreach($data['columns'] AS $cKey => $cValue){
						echo '<td>'.$cValue['display_name'].'</td>'."\n";
					}
					echo "</tr>\n";
				}
				foreach($data['data'] as $dKey => $dValue){
					echo "<tr>\n";
					foreach($dValue as $vKey => $vValue){
						echo '<td class="'.$data['columns'][$vKey]['class'].'">'.$vValue.'</td>'."\n";
					}
					echo "</tr>\n";
				}
				echo "</table>\n";
			}
			else{

			}
			xhtmlFooter();
		}
	}

	function _json($data = NULL){
		if(AJAX){
			if($data){
				echo json_encode($data);
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
