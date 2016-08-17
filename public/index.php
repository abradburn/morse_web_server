<?php

define('BASEPATH', substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), '/')).'/');

define('SYSPATH', BASEPATH.'system/');

define('APPPATH', BASEPATH.'application/');

  // Load custom error logging class.
$_log = SYSPATH.'core/error_log.php';
if(!is_readable($_log)){
	exit("There was an error reading \"error_log.php\".  Make sure that the file exists,and that the folder heirarchy is correctly assembled.\n");
}
require_once $_log;
unset($_log);

register_shutdown_function(array('_error_log', '_static_fatal'));
set_error_handler(array('_error_log', '_static_error'));
set_exception_handler(array('_error_log', '_static_exception'));
ini_set('display_errors', 'off');
error_reporting(E_ALL);

if(isset($_SERVER['HTTP_HOST'])){
	define('HOST', $_SERVER['HTTP_HOST']);
	define('SITEURI', 'http://'.$_SERVER['HTTP_HOST'].'/');
	  // The current defenition of SITEURI does not take into account the possibility of "https://".
}else{
	define('SITEURI', NULL);
}

$_init = SYSPATH.'core/init.php';
if(!is_readable($_init)){
// Redirect this to error logging?
	exit("There was an error reading \"init.php\".  Make sure that the file exists, and that the folder heirarchy is correctly assembled.\n");
}

require_once $_init;
unset($_init);

?>
