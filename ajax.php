<?php
require_once('config.php');
$loader=new loader();
$resistry=new resistry();
$data=new basedata($loader,$resistry);
$data->resistry->__set('data',new data());
$data->resistry->__set('helper',new helper());
 $data->getinclude($_GET['file']);

?>