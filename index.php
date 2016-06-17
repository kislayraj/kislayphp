<?php
require_once('config.php');
$resistry=new appManger();
$resistry->process();
$resistry->render();
unset($resistry);
?>