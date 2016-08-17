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
					array(
						'type' => 'javascript',
						'url' => 'javascript/morse.js'
					),
					array(
						'type' => 'css',
						'url' => 'https://fonts.googleapis.com/css?family=Slabo+27px'
					),
					array(
						'type' => 'css',
						'url' => 'css/morse.css'
					)
				)
			);
			xhtmlHeader($include);
			if($data){
?>
<div><h2>Current Transmission:</h2></div>
<div id="content"><p>There is no content.</p></div>
<div id="selection">
	<form id="select_submission">
		<fieldset id="device_select">
		<legend>Device:</legend>
		<select id="device">
			<option value="">NONE</option>
		</select>
		</fieldset>
		<fieldset id="submission_select">
		<legend>Content:</legend>
			<table>
			</table>
		</fieldset>
		<fieldset id="">
			<span id="submit" class="button">Transmit</span>
			<span id="stop" class="button">Stop Transmission</span>
		</fieldset>
	</form>
</div>
<?php
			}
			else{
				echo "<p>The index has been loaded.</p>\n";
			}
			xhtmlFooter();
		}
	}

	function empty_response($data = NULL){
		if(AJAX){
			if($data){
				echo " ";
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
				echo "<p>The index has been loaded.</p>\n";
			}
			else{
				echo "<p>The index has been loaded.</p>\n";
			}
			xhtmlFooter();
		}
	}

	function json($data = NULL){
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

			if(isset($data['json'])){
				echo json_encode($data['json']);
			}
		}
	}
?>
