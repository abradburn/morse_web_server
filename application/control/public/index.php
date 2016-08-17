<?php
if(!defined('BASEPATH')){exit('No direct script access allowed.');}
class index extends _controller{

	private $morseduino;

	private $queue_length = 135;

	// Load the variables into a accessible area.
	private $device = array(
		'id' => NULL
	);

	public function __construct(){
		parent::__construct();

		if(!$this->load -> _load_mysql($this->morseduino, 'morseduino', 'write')){
			trigger_error('', E_USER_NOTICE);
			echo "Error creating the database connection.\n";
			return FALSE;
		}

		if(!$this->input->_secured_input($this->device['id'], array('device_id' => 'string'))){
			trigger_error('No device id found in request.');
		}

		if($this->device['id'] == ''){
			$this->device['id'] = NULL;
		}
	}

	public function __destruct(){

	}

	public function index(){
		if(isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_USER_AGENT'])){
			trigger_error('Request Details: '.$_SERVER['REMOTE_ADDR'].', '.$_SERVER['HTTP_USER_AGENT'], E_USER_NOTICE);
		}else{
			trigger_error('Command line request.', E_USER_NOTICE);
		}

		$data = TRUE;
		$this->_load_view('index');
		index($data);
	}

	public function get_device_list(){
		$query = 'SELECT `dr`.`device_id`, `dr`.`location` FROM `device_registration` AS `dr`;';

		if(!$this->morseduino -> _query($devices, $query, FALSE)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		$out = array();

		foreach($devices AS $dkey => $dvalue){
			array_push($out, array('device_id' => $dvalue['device_id'], 'location' => $dvalue['location']));
		}

		$data['json'] = $out;
		unset($devices);
		$this->_load_view('index');
		json($data);

		return TRUE;
	}

	public function get_index(){
		if(is_null($this->device['id'])){
			$out = array('submission_id' => NULL, 'index' => NULL);

			$data['json'] = $out;
			$this->_load_view('index');
			json($data);
			return FALSE;
		}

		$query = 'SELECT `tn`.`submission_id`, `tn`.`start_index` + `tn`.`substring_index` AS `index` FROM `transmission_next` AS `tn` WHERE `tn`.`device_id` = "'.$this->device['id'].'";';

		if(!$this->morseduino -> _query($index, $query)){
			trgger_error('', E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		$out = array('submission_id' => (int) $index['submission_id'], 'index' => (int) $index['index']);

		$data['json'] = $out;
		unset($index);
		$this->_load_view('index');
		json($data);

		return TRUE;
	}

	public function get_current_transmission(){
		if(is_null($this->device['id'])){
			return FALSE;
		}

		$query = 'SELECT `tn`.`submission_id`, `sub`.`content`, `sub`.`name`, `sub`.`description`, `sub`.`source` FROM `transmission_next` AS `tn` JOIN `submissions` AS `sub` ON `tn`.`submission_id` = `sub`.`submission_id` WHERE `tn`.`device_id` = "'.$this->device['id'].'";';

		if(!$this->morseduino -> _query($content, $query)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		$out = array('submission_id' => (int) $content['submission_id'], 'content' => $content['content'], 'name' => $content['name'], 'description' => $content['description'], 'source' => $content['source']);

		$data['json'] = $out;
		unset($content);
		$this->_load_view('index');
		json($data);
	}

	public function get_submissions(){
		$query = 'SELECT `submission_id`, SUBSTRING(`content`, 1, 40) AS `content`, `name`, `description`, `source` FROM `submissions`;';

		if(!$this->morseduino -> _query($result, $query, FALSE)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		$out = array();

		foreach($result as $rkey => $rvalue){
			array_push($out, array('submission_id' => (int) $rvalue['submission_id'], 'content' => $rvalue['content'], 'name' => $rvalue['name'], 'description' => $rvalue['description'], 'source' => $rvalue['source']));
		}
		unset($result);
		unset($rkey);
		unset($rvalue);

		$data['json'] = $out;
		unset($out);
		$this->_load_view('index');
		json($data);

		return TRUE;
	}

// IN PROGRESS
	public function set_transmission(){
		if(is_null($this->device['id'])){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		if(!$this->input -> _secured_input($submission_id, array('submission_id' => 'int'))){
			trigger_error('Error fetching submission_id from input.', E_USER_NOTICE);
			return FALSE;
		}

		if(!$this->_submission_id_check($submission_id)){
			trigger_error('Bad submission_id.', E_USER_NOTICE);
			return FALSE;
		}

		$this->_stop_transmission($this->device['id']);

		  // Add an entry to the transmission_next table for the desired submission.
		$query = 'INSERT INTO `transmission_next` (`device_id`, `submission_id`, `start_index`, `substring_index`, `revolving`) VALUES ("'.$this->device['id'].'", '.$submission_id.', 1, 0, 0) ON DUPLICATE KEY UPDATE `submission_id` = VALUES(`submission_id`), `start_index` = VALUES(`start_index`), `substring_index` = VALUES(`substring_index`), `revolving` = VALUES(`revolving`);';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);
		unset($result);

		  // Add an entry to the command_queue table.
		$query = 'INSERT INTO `command_queue` (`device_id`, `command_id`) VALUES ("'.$this->device['id'].'", 200);';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);
		unset($result);
	}

	private function _submission_id_check($submission_id = NULL){
		if(is_null($submission_id)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$query = 'SELECT TRUE FROM `submissions` AS `sub` WHERE `sub`.`submission_id` = '.$submission_id.';';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}

		if($result == 0){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}
		
		return TRUE;
	}

	private function _stop_transmission(&$device_id = NULL){
		if(is_null($device_id)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		  // Clear the 'transmission_next' table for the device.
		$query = 'DELETE FROM `transmission_next` WHERE `device_id` = "'.$device_id.'";';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($query, E_USER_NOTICE);
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($result);
		unset($query);

		  // Qeueu a stop command for the device.
		$query = 'INSERT INTO `command_queue` (`device_id`, `command_id`) VALUES ("'.$device_id.'", 90);';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($result);
		unset($query);

		return TRUE;
	}

	public function stop_transmission(){
		if(is_null($this->device['id'])){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$this->_stop_transmission($this->device['id']);
	}

	public function cron(){
		  // Lets check for idelness among all devices registered as http_client.
		$query = 'SELECT `dr`.`device_id` FROM `device_registration` AS `dr` WHERE `http_client` = true;';

		if(!$this->morseduino -> _query($devices, $query, FALSE)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		foreach($devices as $dkey => $dvalue){
			$this->_idle_check($dvalue['device_id']);
		}
		unset($devices);
		unset($dkey);
		unset($dvalue);

		return TRUE;
	}

	private function _idle_check($device_id = NULL){
		if(is_null($device_id)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		  // Are there any new submissions?
		$query = 'SET @DEVICE_ID = "'.$device_id.'"; SELECT COUNT(`sub`.`submission_id`) AS `count` FROM `submissions` AS `sub` WHERE `sub`.`submission_id` NOT IN (SELECT `subc`.`submission_id` FROM `submissions_completed` AS `subc` WHERE `subc`.`device_id` = "'.$device_id.'" AND `subc`.`revolving` = false);';

		if(!$this->morseduino -> _query($submissions, $query)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		  // Are there any new submissions
		$query = 'SET @DEVICE_ID = "'.$device_id.'"; SELECT COUNT(`rm`.`message_id`) AS `count` FROM `revolving_messages` AS `rm` LEFT JOIN (SELECT * FROM `submissions_completed` AS `subc` WHERE `subc`.`revolving` = TRUE AND `subc`.`device_id` = @DEVICE_ID) AS `comp` ON `rm`.`message_id` = `comp`.`submission_id` WHERE TIMESTAMPDIFF(MINUTE, `comp`.`date_time`, NOW()) > `rm`.`interval` OR `comp`.`date_time` IS NULL;';

		if(!$this->morseduino -> _query($revolving, $query)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		if($submissions == 0 && $revolving == 0){
			trigger_error('There is nothing to transmit.  We are going to exit.', E_USER_NOTICE);
			return FALSE;
		}

		$query = 'SELECT TIMESTAMPDIFF(MINUTE, `tn`.`date_time`, NOW()) FROM `transmission_next` AS `tn` WHERE `tn`.`device_id` = "'.$device_id.'";';

		if(!$this->morseduino -> _query($elapsed, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}

		if(!$this->_history_check($device_id) || $elapsed >= 10){
			if($submissions > 0 || $revolving > 0){
				trigger_error('Ther is something to transmit, and nothing is currently being transmitted. Let\'s queue a command.', E_USER_NOTICE);
				if(!$this->_command_queue_request($device_id)){
					trigger_error('', E_USER_NOTICE);
					return FALSE;
				}

				return TRUE;
			}
		};

		return TRUE;
	}

	private function _command_queue_request($device_id = NULL){
		if(is_null($device_id)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$query = 'INSERT INTO `command_queue` (`device_id`, `command_id`) VALUES ("'.$device_id.'", 200) ON DUPLICATE KEY UPDATE `command_id` = VALUES(`command_id`);';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($result);
		unset($query);

		return TRUE;
	}

	public function queue_status(){
		//trigger_error('queue status called.', E_USER_WARNING);

		if(!$this->input->_secured_input($error_status, array('error' => 'bool'))){
			trigger_error('Error fetching error status.', E_USER_NOTICE);
		}

		if($error_status){
			trigger_error('Error encounter by the client device.', E_USER_NOTICE);
			return FALSE;
		}

		if(!$this->input -> _secured_input($function, array('function' => 'string'))){
			trigger_error('Error getting function.', E_USER_NOTICE);
			return FALSE;
		}

		if(!$this->_command_queue_check($this->device['id'])){
			trigger_error('Stop command queued, we aren\'t going to respond to requests for submission data until it is fetched.', E_USER_NOTICE);

			if($function == 'index_update'){
				$data = TRUE;
			}elseif($function == 'request'){
				$data['json'] = array("App" => array("Configure" => array("output" => '')));
			}

			$this->_load_view('index');
			json($data);

			return FALSE;
		}

		if($function == 'index_update'){
			trigger_error('Updating the index for `transmission_next`.', E_USER_NOTICE);
			if(!$this->input -> _secured_input($index, array('index' => 'int'))){
				trigger_error('Error getting the index value.', E_USER_NOTICE);
				return FALSE;
			}

			if(!$this->_update_queue_state($this->device['id'], $index)){
				echo "Error updating queue state.\n";
				return FALSE;
			}

			$data['json'] = array('result' => 'true');
		}elseif($function == 'request'){
			  // Is there a submission in progress?
			if(!$this->_history_check($this->device['id'])){
				trigger_error('History Check failed for the device, we are now going to select a submission.', E_USER_NOTICE);
				  // There is no submission in progress, are there any new submissions?
				if(!$this->_submission_select($this->device['id'])){
					  // There are no new submissions, there is nothing for the device to transmit.
					  // Return Nothing.
					trigger_error('', E_USER_NOTICE);
					return FALSE;
				}
			}

			trigger_error('History Check succeded for the device, we are going to get the `revolving` value for the entry.', E_USER_NOTICE);
			if(!$this->_get_transmission_type($this->device['id'], $revolving)){
				trigger_error('', E_USER_NOTICE);
				return FALSE;
			}

			  // There is a submission in progress. Is have we transmitted (morse code) the entire submission?
			if(!$this->_index_check($this->device['id'], $revolving)){
				trigger_error('Index Check failed for the entry in `transmission_next` so we are going to clear the entry.', E_USER_NOTICE);
				  // We have transmitted (morse code) the entire submission, lets take care of adding it to the list of completed submissions, and clear the transmission_next table.
				if(!$this->_submission_complete($this->device['id'])){
					trigger_error('', E_USER_NOTICE);
					return FALSE;
				}

				trigger_error('The entry was cleared, lets select our next submission.', E_USER_NOTICE);
				  // Now that we've taken care of the paperwork, are there any new submissions to transmit?
				if(!$this->_submission_select($this->device['id'])){
					  // There are no new submissions, there is nothing for the device to transmit.
					  // Return Nothing.
					trigger_error('Nothing for the device to transmit, we are exiting.', E_USER_NOTICE);
					return FALSE;
				}
			}

			unset($revolving);
			trigger_error('Checking the transmission type one more time, in case it changed.', E_USER_NOTICE);
			if(!$this->_get_transmission_type($this->device['id'], $revolving)){
				trigger_error('', E_USER_NOTICE);
				return FALSE;
			}

			if(!$this->_get_queue_content($this->device['id'], $output, $revolving)){
				trigger_error('Error getting queue content.', E_USER_NOTICE);
				return FALSE;
			}

			$data['json'] = array("App" => array("Configure" => array("output" => $output)));
		}else{
			trigger_error('The definition of function was not anticipated.  We are simply going to quietly exit.', E_USER_NOTICE);
			return FALSE;
		}

		$this->_load_view('index');
		json($data);
	}

/*
 *
 *
 *
 *
 */

	private function _submission_complete($device_id = NULL){
		if(is_null($device_id)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$query = 'INSERT INTO `submissions_completed` (`device_id`, `submission_id`, `revolving`) (SELECT `tn`.`device_id`, `tn`.`submission_id`, `tn`.`revolving` FROM `transmission_next` AS `tn` WHERE `tn`.`device_id` = "'.$device_id.'");';
		if(!$this->morseduino -> _query($result, $query)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$query = 'DELETE FROM `transmission_next` WHERE `device_id` = "'.$device_id.'";';
		if(!$this->morseduino -> _query($result, $query)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		return TRUE;
	}

	private function _submission_select($device_id = NULL){
		if(is_null($device_id)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		// Do we need to transmit a revolving messages, based upon the time that it was last transmitted, and the current time?

		trigger_error('Going to select a revolving message for transmission if it is due.', E_USER_NOTICE);
		  // The revolving messages need to be selectable.  The challenge here is that we don't know how to handle it.  These messages are in a different table, with the same ids.
		$query = 'SET @DEVICE_ID = "'.$device_id.'"; INSERT INTO `transmission_next` (`device_id`, `submission_id`, `start_index`, `substring_index`, `revolving`) SELECT @DEVICE_ID, `rm`.`message_id`, 1, 0, 1 FROM `revolving_messages` AS `rm` LEFT JOIN (SELECT `subc`.`submission_id`, MAX(`subc`.`date_time`) AS `date_time` FROM `submissions_completed` AS `subc` JOIN `revolving_messages` AS `rm` ON `subc`.`submission_id` = `rm`.`message_id` WHERE `subc`.`revolving` = TRUE AND `subc`.`date_time` > TIMESTAMP(DATE_SUB(NOW(), INTERVAL `rm`.`interval` MINUTE)) GROUP BY `subc`.`submission_id`) AS `comp` ON `rm`.`message_id` = `comp`.`submission_id` WHERE TIMESTAMPDIFF(MINUTE, `comp`.`date_time`, NOW()) > `rm`.`interval` OR `comp`.`date_time` IS NULL ON DUPLICATE KEY UPDATE `submission_id` = VALUES(`submission_id`), `start_index` = VALUES(`start_index`), `substring_index` = VALUES(`substring_index`);';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);
		unset($result);

		if($this->_history_check($device_id)){
			trigger_error('A revolving message was found, and populated in the `transmission_next` table.', E_USER_NOTICE);
			return TRUE;
		}

		trigger_error('Selecting a value from the standard submissions, because no revolving messages were selected.', E_USER_NOTICE);
		  // No revolving messages to be sent, lets send the next available submission.
		$query = 'SET @DEVICE_ID = "'.$device_id.'"; INSERT INTO `transmission_next` (`device_id`, `submission_id`, `start_index`, `substring_index`, `revolving`) SELECT @DEVICE_ID, `sub`.`submission_id`, 1, 0, 0 FROM `submissions` AS `sub` WHERE `sub`.`submission_id` NOT IN (SELECT `subc`.`submission_id` FROM `submissions_completed` AS `subc` WHERE `subc`.`device_id` = @DEVICE_ID AND `subc`.`revolving` = 0 AND `subc`.`revolving` = FALSE) ORDER BY `sub`.`date_time` DESC LIMIT 1 ON DUPLICATE KEY UPDATE `submission_id` = VALUES(`submission_id`), start_index = VALUES(`start_index`), `substring_index` = VALUES(`substring_index`);';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);
		unset($result);

		return TRUE;
	}

	public function register_device(){
		if(is_null($this->device['id'])){
			trigger_error($this->device['id'], E_USER_NOTICE);
			return FALSE;
		}

		if(!$this->input -> _secured_input($http_client, array('http_client' => 'string'))){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		if(!isset($_SERVER['REMOTE_ADDR'])){
			trigger_error('Command line test.', E_USER_NOTICE);
			$ip_address = '127.0.0.1';
		}else{
			$ip_address = $_SERVER['REMOTE_ADDR'];
		}

		if(!$ip_address = ip2long($ip_address)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$query = 'INSERT INTO `device_registration` (`device_id`, `http_client`, `ip_address`) VALUES (\''.$this->device['id'].'\', '.$http_client.', '.$ip_address.') ON DUPLICATE KEY UPDATE `http_client` = VALUES(`http_client`), `ip_address` = VALUES(`ip_address`);';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);
		unset($result);


		  // Output
		$data['json'] = array("App" => array("Config" => array("response" => "success")));
		$this->_load_view('index');
		json($data);
	}

	private function _update_queue_state($device_id = NULL, $index = NULL){
		if(is_null($device_id) || is_null($index)){
			echo "Invanlid input.\n";
			return FALSE;
		}

		$query = "UPDATE `transmission_next` AS `tn` SET `tn`.`substring_index` = ".$index." WHERE `tn`.`device_id` = \"".$device_id."\";";

		if(!$this->morseduino -> _query($result, $query)){
			return FALSE;
		}
		unset($query);
		unset($result);

		return TRUE;
	}

	private function _history_check(&$device_id = NULL){
		if(is_null($device_id)){
			echo "Invalid input.\n";
			return FALSE;
		}

		$query = 'SELECT TRUE FROM `transmission_next` AS `nt` WHERE `nt`.`device_id` = \''.$device_id.'\';';

		if(!$this->morseduino -> _query($result, $query)){
			return FALSE;
		}
		unset($query);

		if($result == true){
			unset($result);

			trigger_error('History Check found an entry in `transmission_next` for the device.', E_USER_NOTICE);
			return TRUE;
		}
		unset($result);

		trigger_error('History Check did not find an entry in `transmission_next` for the device.', E_USER_NOTICE);
		return FALSE;
	}

	private function _command_queue_check(&$device_id = NULL){
		$query = 'SELECT `cq`.`transaction_id` FROM `command_queue` AS `cq` WHERE `device_id` = "'.$device_id.'" AND `cq`.`command_id` = 90;';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}

		if(is_null($result)){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	private function _get_transmission_type($device_id = NULL, &$revolving = NULL){
		if(is_null($device_id) || !is_null($revolving)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$query = 'SELECT `tn`.`revolving` FROM `transmission_next` AS `tn` WHERE `tn`.`device_id` = "'.$device_id.'";';

		if(!$this->morseduino -> _query($revolving, $query)){
			trigger_error($revolving, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		return TRUE;
	}

	private function _index_check($device_id = NULL, $revolving = NULL){
		if(is_null($device_id) || is_null($revolving)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		if($revolving){
			$query = 'SELECT `tn`.`start_index` + `tn`.`substring_index` AS `index`, CHAR_LENGTH(`rm`.`message`) AS `length` FROM `transmission_next` AS `tn` JOIN `revolving_messages` AS `rm` ON `tn`.`submission_id` = `rm`.`message_id` WHERE `tn`.`device_id` = "'.$device_id.'";';

		}else{
			$query = 'SELECT `tn`.`start_index` + `tn`.`substring_index` AS `index`, CHAR_LENGTH(`sub`.`content`) AS `length` FROM `transmission_next` AS `tn` JOIN `submissions` AS `sub` ON `tn`.`submission_id` = `sub`.`submission_id` WHERE `tn`.`device_id` = "'.$device_id.'";';
		}

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		if($result['index'] >= $result['length']){
			// The submission is done, and we need to move the entry into the log, while providing the next submission/or abandoning the idea of returning a submission at all.
			return FALSE;
		}

		  // The submission has not been exhausted, and we should continue by returning the next segment.
		return TRUE;
	}

	private function _get_queue_content($device_id = NULL, &$output = NULL, $revolving = NULL){
		if(is_null($device_id) || !is_null($output) || is_null($revolving)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		if($revolving){
			$query = 'SET @INDEX = (SELECT `tn`.`start_index` + `tn`.`substring_index` FROM `transmission_next` AS `tn` WHERE `tn`.`device_id` = "'.$device_id.'"); SELECT SUBSTRING(`rm`.`message`, @INDEX, '.$this->queue_length.') AS `selection` FROM `transmission_next` AS `tn` JOIN `revolving_messages` AS `rm` ON `tn`.`submission_id` = `rm`.`message_id` WHERE `tn`.`device_id` = "'.$device_id.'";';
		}else{
			$query = 'SET @INDEX = (SELECT `tn`.`start_index` + `tn`.`substring_index` FROM `transmission_next` AS `tn` WHERE `tn`.`device_id` = "'.$device_id.'"); SELECT SUBSTRING(`sub`.`content`, @INDEX, '.$this->queue_length.') AS `selection` FROM `transmission_next` AS `tn` JOIN `submissions` AS `sub` ON `tn`.`submission_id` = `sub`.`submission_id` WHERE `tn`.`device_id` = "'.$device_id.'";';
		}

		if(!$this->morseduino -> _query($output, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		$query = 'UPDATE `transmission_next` AS `tn` SET `tn`.`start_index` = @INDEX;';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($result);
		unset($query);

		return TRUE;
	}

	public function command_get(){
		if($this->_check_queue($this->device['id'], $command, $queued)){
			$data['json'] = array("App" => array("Command" => array("command" => (int) $command, "queued" => (int) $queued - 1)));
		}else{
			$data['json'] = array("App" => array("Command" => array("command" => 0, "queued" => 0)));

		}

		  // Output
		$this->_load_view('index');
		json($data);
	}

	private function _check_queue($device_id, &$command = NULL, &$queued = NULL){
		if(is_null($device_id) || !is_null($command) || !is_null($queued)){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$query = 'SET @DEVICE_ID = "'.$device_id.'"; SELECT COUNT(`cq`.`device_id`) AS `queued` FROM `command_queue` AS `cq` WHERE `cq`.`device_id` = @DEVICE_ID;';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		  // If value is non-zero, proceed.
		  // If value is zero, we can terminate, with an empty json object.
		if($result == 0){
			trigger_error('', E_USER_NOTICE);
			return FALSE;
		}

		$queued = $result;
		unset($result);

		$query = 'SET @DEVICE_ID = "'.$device_id.'"; SELECT `cq`.`transaction_id`, `cq`.`command_id` FROM `command_queue` AS `cq` WHERE `cq`.`device_id` = @DEVICE_ID AND `cq`.`submit_time` = (SELECT MIN(`cq`.`submit_time`) FROM `command_queue` AS `cq` WHERE `cq`.`device_id` = @DEVICE_ID) LIMIT 1;';

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);

		$transaction_id = $result['transaction_id'];
		$command = $result['command_id'];
		unset($result);

		$query = "SET @TRANSACTION_ID = ".$transaction_id."; INSERT INTO `command_log` (`transaction_id`, `device_id`, `command_id`, `submit_time`) SELECT `transaction_id`, `device_id`, `command_id`, `submit_time` FROM `command_queue` WHERE `transaction_id` = @TRANSACTION_ID ON DUPLICATE KEY UPDATE `device_id` = VALUES(`device_id`), `command_id` = VALUES(`command_id`), `submit_time` = VALUES(`submit_time`); DELETE FROM `command_queue` WHERE `transaction_id` = @TRANSACTION_ID;";

		if(!$this->morseduino -> _query($result, $query)){
			trigger_error($result, E_USER_NOTICE);
			return FALSE;
		}
		unset($query);
		unset($result);

		return TRUE;
	}
}
?>
