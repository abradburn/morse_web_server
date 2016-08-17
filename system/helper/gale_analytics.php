<?php
if(!defined('BASEPATH')){exit('No direct script access allowed.');}
  // Class names must be in all lower case to be routed correctly.
class gale_analytics extends _controller{

/*
	Available resources provided by the parent class include:

	$this->load;

	$this->input;

	$this->load_view();

	$this->load_helper();
*/

	private $HEADERS = array(
		'Connection: keep-alive',
		'Origin: http://analytics.gale.com',
		'X-Requested-With: XMLHttpRequest',
		'cache-control: no-cache',
		'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/41.0.2272.76 Chrome/41.0.2272.76 Safari/537.36',
		'Referer: http://analytics.gale.com/gallery/',
		'Accept-Encoding: gzip, deflate',
		'Accept-Language: en-US,en;q=0.8'
	);

	private $session = NULL;

	private $url_base = 'http://analytics.gale.com/';

	private $session_id;

	private $user_id;

	  // The number of seconds to wait for a job to be complete;
	private $job_wait = 60;

	public function __construct($email_address = NULL, $password = NULL){
		parent::__construct();

		if(is_null($email_address) || is_null($password)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

/*
		$temp = curl_init();

		  // Enable Cookies.
		curl_setopt($temp, CURLOPT_COOKIEFILE, '');

		  // Used to ensure that the response is returned to a variable for evaluation.
		curl_setopt($temp, CURLOPT_RETURNTRANSFER, TRUE);

	  // For Testing:
	curl_setopt($temp, CURLINFO_HEADER_OUT, TRUE);
*/

		if(!$this->pre_auth($pre_auth, $this->url_base.'gallery/api/auth/preauth/', $email_address)){

		}

		if(!$this->decode_parameters($auth_param, $pre_auth['parameters'])){

		}
		unset($pre_auth);

		if(!$this->login($session_details, $this->url_base.'gallery/api/auth/sessions/', $auth_param, $email_address, $password)){

		}

		$this->session_id = $session_details['session_id'];
		$this->user_id = $session_details['user_id'];

		unset($auth_param);

		
	}

	public function __destruct(){

	}

	private function decode_parameters(&$result = NULL, $array = NULL){
		if(!is_null($result) || is_null($array)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$parameters = array();

		foreach($array as $key => $obj){
			$parameters[$obj['name']] = $obj['value'];
		}

		$result = $parameters;

		return TRUE;
	}

	private function pre_auth(&$result = NULL, $url = NULL, $email){
		if(!is_null($result) || is_null($url)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$HEADER = array(
			'Content-Type: application/json'
		);

		$HEADER = array_merge($HEADER, $this->HEADERS);

		$POST = array(
			'scheme' => 'alteryx',
			'parameters' => array(
				array(
					'name' => 'email',
					'value' => $email
				)
			)
		);

		$POST = json_encode($POST);

		$temp = curl_init();

		  // Used to ensure that the response is returned to a variable for evaluation.
		curl_setopt($temp, CURLOPT_RETURNTRANSFER, TRUE);

		  // Set target URL.
		curl_setopt($temp, CURLOPT_URL, $url);

		  // Make sure that some additional header parameters are set.
		curl_setopt($temp, CURLOPT_HTTPHEADER, $HEADER);

		  // Enable submission of data through POST.
		curl_setopt($temp, CURLOPT_POST, TRUE);
		curl_setopt($temp, CURLOPT_POSTFIELDS, $POST);

		  // Set user agent (browser identification).
		//curl_setopt($temp, CURLOPT_REFERER, 'http://analytics.gale.com/gallery');

		$response = curl_exec($temp);

		$result = json_decode($response, TRUE);

		return TRUE;
	}

	private function login(&$result = NULL, $url, $parameters, $email = NULL, $password = NULL){
		if(!is_null($result) || is_null($url) || !is_array($parameters)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$password = hash_hmac('SHA256', $parameters['nonce'].'_'.crypt(hash_hmac('SHA256', $password, $parameters['hmacKey']), $parameters['salt']), $parameters['hmacKey']);

		$HEADER = array(
			'Content-Type: application/json',
			'X-Authorization: SPECIAL '.$this->session_id
		);

		$HEADER = array_merge($HEADER, $this->HEADERS);

		$POST = array(
			'scheme' => 'alteryx',
			'parameters' => array(
				array(
					'name' => 'email',
					'value' => $email
				),
				array(
					'name' => 'password',
					'value' => $password
				),
				array(
					'name' => 'nonce',
					'value' => $parameters['nonce']
				)
			)
		);

		$POST = json_encode($POST);

		$temp = curl_init();

		  // Used to ensure that the response is returned to a variable for evaluation.
		curl_setopt($temp, CURLOPT_RETURNTRANSFER, TRUE);
		
		  // Set target URL.
		curl_setopt($temp, CURLOPT_URL, $url);

		  // Make sure that some additional header parameters are set.
		curl_setopt($temp, CURLOPT_HTTPHEADER, $HEADER);

		  // Enable submission of data through POST.
		curl_setopt($temp, CURLOPT_POST, TRUE);
		curl_setopt($temp, CURLOPT_POSTFIELDS, $POST);

		$response = curl_exec($temp);

		$response = json_decode($response, TRUE);

		$result = array(
			'session_id' => $response['sessionId'],
			'user_id' => $response['user']['id']
		);

		return TRUE;
	}

	public function upload_csv(&$result = NULL, $file = NULL){
var_dump($result);
var_dump($file);
		if(!is_null($result) || is_null($file)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$URL = $this->url_base.'gallery/api/admin/users/tempfiles/';

		$HEADER = array(
			'Content-Type: multipart/form-data',
			'X-Authorization: SPECIAL '.$this->session_id
		);

		$HEADER = array_merge($HEADER, $this->HEADERS);

		$curl_file = curl_file_create($file, 'text/csv');

		$POST = array(
				'mode' => 'DW2',
				'sessionId' => $this->session_id,
				'spatialOnly' => 'false',
				'fileSpecString' => 'null',
				'responseFormat' => 'json',
				'inputFile' => $curl_file
		);

		$temp = curl_init();

		  // Used to ensure that the response is returned to a variable for evaluation.
		curl_setopt($temp, CURLOPT_RETURNTRANSFER, TRUE);

		  // Set target URL.
		curl_setopt($temp, CURLOPT_URL, $URL);

		  // Make sure that some additional header parameters are set.
		curl_setopt($temp, CURLOPT_HTTPHEADER, $HEADER);

		  // Enable submission of data through POST.
		curl_setopt($temp, CURLOPT_POST, TRUE);
		curl_setopt($temp, CURLOPT_POSTFIELDS, $POST);

var_dump(curl_getinfo($temp, CURLINFO_HEADER_OUT));

		$response = curl_exec($temp);
var_dump($response);

		$response = json_decode($response, TRUE);

		$result = $response['id'];

		return TRUE;
	}

	public function start_job(&$result = NULL, $file_id = NULL){
		if(!is_null($result) || is_null($file_id)){
var_dump($result);
var_dump($file_id);
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$URL = $this->url_base.'gallery/api/apps/jobs/';

		$HEADER = array(
			'Content-Type: application/json',
			'X-Authorization: SPECIAL '.$this->session_id
		);

		$HEADER = array_merge($HEADER, $this->HEADERS);

		$POST = '{"appPackage":{"id":"5474eeaad1b3010ab8f59efd"},"userId":"550326f0d1b3010a50323ead","appName":"Marketing Action Analysis (Patrons).yxwz","questions":[{"name":"Name","answer":"\"Orlando Public Library\""},{"name":"Address","answer":"\"101 E Central Blvd\""},{"name":"City","answer":"\"Orlando\""},{"name":"Drop Down (602)","answer":"[{\"key\":\"FL\",\"value\":true}]"},{"name":"Zip","answer":"\"32801\""},{"name":"Check Box (755)","answer":"false"},{"name":"Single Field Address","answer":"true"},{"name":"Input Data Browse Single Address Field","answer":"{\"fileId\":\"'.$file_id.'\",\"fieldMap\":[{\"Key\":\"AddressFull\",\"Value\":\"address\"},{\"Key\":\"Date\",\"Value\":\"date\"},{\"Key\":\"Checkouts (Optional)\",\"Value\":\"\"},{\"Key\":\"Email Address (Optional)\",\"Value\":\"\"}]}"},{"name":"Multiple Field Address","answer":"false"},';
		$POST .= '{"name":"Input Data Browse Multiple Address Fields","answer":"{\"fileId\":\"\"}"},{"name":"Radio Button (710)","answer":"true"},{"name":"Radio Button (709)","answer":"false"},{"name":"Date (582)","answer":"\"\""},{"name":"Date (583)","answer":"\"\""},{"name":"Check Box (597)","answer":"true"},{"name":"Tree (353)","answer":"[\"|0\",\"|1\",\"|2\",\"|3\",\"|4\",\"|5\",\"|6\",\"|7\",\"|8\",\"|9\",\"|10\",\"|11\",\"|12\",\"|13\",\"|14\",\"|15\",\"|16\",\"|17\",\"|18\",\"|19\"]"},{"name":"Check Box (598)","answer":"false"},{"name":"List Box (346)","answer":"[{\"key\":\"A\",\"value\":true},{\"key\":\"B\",\"value\":true},{\"key\":\"C\",\"value\":true},{\"key\":\"D\",\"value\":true},{\"key\":\"E\",\"value\":true},{\"key\":\"F\",\"value\":true},{\"key\":\"G\",\"value\":true},{\"key\":\"H\",\"value\":true},{\"key\":\"I\",\"value\":true},{\"key\":\"J\",\"value\":true},{\"key\":\"K\",\"value\":true},{\"key\":\"L\",\"value\":true},{\"key\":\"U\",\"value\":true}]"},{"name":"Check Box (599)","answer":"false"},{"name":"List Box (355)","answer":"[{\"key\":\"1Y\",\"value\":true},{\"key\":\"5N\",\"value\":true},{\"key\":\"5Y\",\"value\":true},{\"key\":\"5U\",\"value\":true},{\"key\":\"00\",\"value\":true}]"},{"name":"Check Box (600)","answer":"false"},{"name":"List Box (357)","answer":"[{\"key\":\"00\",\"value\":true},{\"key\":\"01-04\",\"value\":true},{\"key\":\"05-09\",\"value\":true},{\"key\":\"10-19\",\"value\":true},{\"key\":\"20-29\",\"value\":true},{\"key\":\"30-49\",\"value\":true},{\"key\":\"50+\",\"value\":true}]"},{"name":"Check Box (601)","answer":"false"},{"name":"List Box (391)","answer":"[{\"key\":\"A\",\"value\":true},{\"key\":\"M\",\"value\":true},{\"key\":\"P\",\"value\":true},{\"key\":\"S\",\"value\":true}]"},{"name":"Radio Button (615)","answer":"true"},{"name":"Geography Selection Tree","answer":"[\"County:12095\"]"},{"name":"Radio Button (614)","answer":"false"},{"name":"Input Custom Geography","answer":"{\"fileId\":\"\"}"},{"name":"Radio Button (738)","answer":"true"},{"name":"Radio Button (741)","answer":"false"},{"name":"Radio Button (739)","answer":"false"},{"name":"Check Box (806)","answer":"false"},{"name":"Check Box (804)","answer":"false"},{"name":"Check Box (805)","answer":"false"}],"jobName":""}';

		$temp = curl_init();

		  // Used to ensure that the response is returned to a variable for evaluation.
		curl_setopt($temp, CURLOPT_RETURNTRANSFER, TRUE);

		  // Set target URL.
		curl_setopt($temp, CURLOPT_URL, $URL);

		  // Make sure that some additional header parameters are set.
		curl_setopt($temp, CURLOPT_HTTPHEADER, $HEADER);

		  // Enable submission of data through POST.
		curl_setopt($temp, CURLOPT_POST, TRUE);
		curl_setopt($temp, CURLOPT_POSTFIELDS, $POST);

		$response = curl_exec($temp);

		$response = json_decode($response, TRUE);

		sleep(1);

		if(!$this->job_complete($response['id'])){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		if(!$this->fetch_output($output, $response['id'])){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$result = array(
			'job_id' => $response['id'],
			'output' => $output
		);

		return TRUE;
	}

	private function job_complete($job_id = NULL){
		if(is_null($job_id)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$start = time();
		$end = $start + $this->job_wait;

		$HEADER = array(
			'X-Authorization: SPECIAL '.$this->session_id
		);

		$HEADER = array_merge($HEADER, $this->HEADERS);
		
		while($start < $end){
			$URL = $this->url_base.'gallery/api/apps/jobs/'.$job_id.'/?_='.time().'000';

			$temp = curl_init();

			// Used to ensure that the response is returned to a variable for evaluation.
			curl_setopt($temp, CURLOPT_RETURNTRANSFER, TRUE);

	      		// Set target URL.
			curl_setopt($temp, CURLOPT_URL, $URL);

		      	// Make sure that some additional header parameters are set.
			curl_setopt($temp, CURLOPT_HTTPHEADER, $HEADER);

			// Enable submission of data through GET.
			curl_setopt($temp, CURLOPT_HTTPGET, TRUE);

			$response = curl_exec($temp);
			$response = json_decode($response, TRUE);

			if($response['status'] != 'Running'){
				return TRUE;
			}
			sleep(1);
		}

		return FALSE;
	}

	private function fetch_output(&$result = NULL, $job_id = NULL){
		if(!is_null($result) || is_null($job_id)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$URL = $this->url_base.'gallery/api/apps/jobs/'.$job_id.'/output/?_='.time().'000';

		$HEADER = array(
			'X-Authorization: SPECIAL '.$this->session_id
		);

		$HEADER = array_merge($HEADER, $this->HEADERS);

		$temp = curl_init();

		  // Used to ensure that the response is returned to a variable for evaluation.
		curl_setopt($temp, CURLOPT_RETURNTRANSFER, TRUE);

	      	// Set target URL.
		curl_setopt($temp, CURLOPT_URL, $URL);

	      	// Make sure that some additional header parameters are set.
		curl_setopt($temp, CURLOPT_HTTPHEADER, $HEADER);

		// Enable submission of data through GET.
		curl_setopt($temp, CURLOPT_HTTPGET, TRUE);

		$response = curl_exec($temp);
		$response = json_decode($response, TRUE);

		$result = $response[0];

		return TRUE;
	}

	public function fetch_file(&$result = NULL, $job_id = NULL, $output_id = NULL, $format = 'Xlsx'){
		if(!is_null($result) || is_null($job_id) || is_null($output_id)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$URL = $this->url_base.'gallery/api/apps/jobs/'.$job_id.'/output/'.$output_id.'/?format='.$format.'&sessionId='.$this->session_id; //.'&attachment=false&_='.time().'000';

		$HEADER = array(
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'Connection: keep-alive',
			'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/41.0.2272.76 Chrome/41.0.2272.76 Safari/537.36',
			'Referer: http://analytics.gale.com/gallery/',
			'Accept-Encoding: gzip, deflate, sdch',
			'Accept-Language: en-US,en;q=0.8'
		);

		$file = APPPATH.'upload/gale/'.$job_id.'.'.strtolower($format);

		if(!$output = fopen($file, 'w')){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$temp = curl_init();

		  // Used to ensure that the response is returned to a variable for evaluation.
		curl_setopt($temp, CURLOPT_RETURNTRANSFER, TRUE);

	      	// Set target URL.
		curl_setopt($temp, CURLOPT_URL, $URL);

	      	// Make sure that some additional header parameters are set.
		curl_setopt($temp, CURLOPT_HTTPHEADER, $HEADER);

		// Enable submission of data through GET.
		curl_setopt($temp, CURLOPT_HTTPGET, TRUE);

		// Enable writing response to file.
		curl_setopt($temp, CURLOPT_FILE, $output);

		$response = curl_exec($temp);

		fclose($output);

		// Enable writing response to file.
		curl_setopt($temp, CURLOPT_FILE, STDOUT);

		  // Used to ensure that the response is returned to a variable for evaluation.
		curl_setopt($temp, CURLOPT_RETURNTRANSFER, TRUE);

		$result = $file;
		
		return TRUE;
	}

	public function delete_job($job_id = NULL){
		if(is_null($job_id)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$URL = $this->url_base.'gallery/api/apps/jobs/'.$job_id.'/';

		$HEADER = array(
			'X-Authorization: SPECIAL '.$this->session_id
		);

		$HEADER = array_merge($HEADER, $this->HEADERS);

		$temp = curl_init();

		  // Used to ensure that the response is returned to a variable for evaluation.
		curl_setopt($temp, CURLOPT_RETURNTRANSFER, TRUE);
		
	      	// Set target URL.
		curl_setopt($temp, CURLOPT_URL, $URL);

	      	// Make sure that some additional header parameters are set.
		curl_setopt($temp, CURLOPT_HTTPHEADER, $HEADER);

		// Enable a DELETE request.
		curl_setopt($temp, CURLOPT_CUSTOMREQUEST, 'DELETE');

		$result = curl_exec($temp);

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
