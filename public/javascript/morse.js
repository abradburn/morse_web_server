$(document).ready(function(){
	console.log('Init.');
	get_devices();
	get_submissions();
	$('#device').change(function(){get_content()});
	$('#submit').click(function(){set_submission()});
	$('#stop').click(function(){stop_transmission()});

	get_index();
	console.log('End Init.');
});

var $content;

var $device = '';

  // Get the transmission text for thise device.
function get_content(){
	$device = $('#device').val();
	//console.log('get_content');
	jQuery.get('index.php', {'class':'index', 'method':'get_current_transmission', 'device_id':$device}, function($data){$content = $data; $('#content').html($data.content); prepare_content();}, 'json');
};

function get_index(){
	//console.log('get_index');
	jQuery.get('index.php', {'class':'index', 'method':'get_index', 'device_id':$device}, function($data){highlight_index($data);}, 'json');
};

function get_devices(){
	//console.log('get_devices');
	jQuery.get('index.php', {'class':'index', 'method':'get_device_list'}, function($data){populate_devices($data);}, 'json');
};

function populate_devices($data){
	//console.log('populate_devices');

	var $new_options = '<option value=\'\'>Select a Device</option>';

	var $i = 0;

	for($i = 0; $i < $data.length; $i++){
		if($data[$i].location == null){
			$new_options += '<option value=\'' + $data[$i].device_id + '\'>' + $data[$i].device_id + '</option>';
		}else{
			$new_options += '<option value=\'' + $data[$i].device_id + '\'>' + $data[$i].location + '</option>';
		}
	}

	$('#device').html($new_options);
};

function get_submissions(){
	//console.log('get_submissions');
	jQuery.get('index.php', {'class':'index', 'method':'get_submissions'}, function($data){populate_submissions($data);}, 'json');
}

function populate_submissions($data){
	//console.log('populate_submissions');

	var $new_input = '';

	var $i = 0;

	for($i = 0; $i < $data.length; $i++){
		$new_input += '<tr><td class=""><input type=\'radio\' name=\'submission\' value=\'' + $data[$i].submission_id + '\'></td><td class="preview">' + $data[$i].content.substring(0, 40) + '...</td></tr><tr><td></td><td class="source"><a href="' + $data[$i].source + '">' + $data[$i].name + '</a></td></tr>';
		if($i != $data.length - 1){
			$new_input += '<br>\n';
		}
	}

	$('#submission_select table').html($new_input);
};

function set_submission(){
	//console.log('Submitted.');

	var $submission = $('input[name=submission]:checked').val();

	if($device == null || $submission == null){
		console.log('Invalid input.');
		return;
	}

	jQuery.get('index.php', {'class':'index', 'method':'set_transmission', 'device_id':$device, 'submission_id':$submission}, function($data){console.log($data)}, 'json');
};

function stop_transmission(){
	//console.log('stop_transmission');

	jQuery.get('index.php', {'class':'index', 'method':'stop_transmission'}, function($data){}, 'json');
};

function highlight_index($data){
	//console.log('highlight_index');

	if($content === undefined || $data.submission_id != $content.submission_id){
		console.log('No submission_id match');
		get_content();
		setTimeout(get_index, 1000);
		return;
	}

	if($data.index >= 3){
		var $target = '#char_' + ($data.index - 3);

		clear_fontweight();
		$($target).css('font-weight', 'bold');
		$($target).css('text-decoration', 'underline');
		$($target).css('font-size', '120%');
	}

	setTimeout(get_index, 500);
};

  // Take the content text, and parse it into something that we can work with for later.
  // Add a span around each character.
  // Label that span with a unique ID.
function prepare_content(){
	//console.log('prepare_content');
	var $content = $('#content');

	$content.each(function(){

		var $this_content = $(this);

		var $new_content = '';

		var $i = 0;

		for($i = 0; $i < $this_content.text().length; $i++){
			var $substring = $this_content.text().substr($i, 1);

			$char_index = $i + 1;

			$new_content += '<span id=\'char_' + $char_index + '\' class=\'morse_char\'>' + $substring + '</span>';
		}

		$this_content.html($new_content);
	});
};

function clear_fontweight(){
	//console.log('clear_fontweight');
	var $chars = $('.morse_char');

	$chars.each(function(){
		$(this).css('font-weight', 'normal');
		$(this).css('text-decoration', 'none');
		$(this).css('font-size', '100%');
	});
};
