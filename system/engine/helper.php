<?php 
class helper {
	 private $helper=array();
	function __call($method,$arg=array()){
	$a=array_shift($arg);
	extract($this->helper);
	return in_array($method,$this->helper) ? $method->$a(implode(',',$arg)) : $method."not found";
	}
	
	function getset($abc,$class){
	require_once(helper.'/'.$class.'.php');
	$this->helper[$abc]=new $class();
	return $this->helper[$abc];
	}
	function __destruct(){
	foreach(get_object_vars($this) as $k=>$v):
    unset($this->$k);
	endforeach;
	}
	
}
?>