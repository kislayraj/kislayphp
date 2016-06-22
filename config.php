<?php
define("ABS_PATH", dirname(__FILE__));
define('Apps',dirname(__FILE__).'/app');
define('System',dirname(__FILE__).'/system/engine/');
define('helper',ABS_PATH.'/system/helper/');
define('CONTROLLER',Apps.'/controller/');
define('MODEL',Apps.'/model/');
define('VIEW',Apps .'/view/');
define('root',ABS_PATH.'/root/');
define('Theme',VIEW.'theme/sample/');
define('Db',dirname(__FILE__).'/system/db/');
define('plugin',ABS_PATH.'/vendor/');
define('WS',false);

define('DefualtClass','commonApp');
function __autoload($class_name) {
		
	include_once System.$class_name.'.php'; 	
	}


?>
