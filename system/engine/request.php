<?php
class request{
	public $reg;
	private $re;
	private $data;
	private $db;
	function __construct($route,$db){
	$this->re=$route;
	$this->db=$db;
	}
	function requestAction($res=array()){
	$class=empty($res) ? $this->re['page']."App" : $res['page']."App";
	$method=empty($res) ? $this->re['action'] : $res['action'];

	$id=empty($res) ? $this->re['id'] : $res['id'];
	if(file_exists(CONTROLLER.$class.".php")){
		include_once(CONTROLLER.$class.".php");
	if(class_exists($class)){
	
	$model=$this->loadModel($this->re['page']);	
	$data=new data();
	if(class_exists($class)){	
	$class=new $class($model,$data,$this);
	if(method_exists($class,$method)){
	
	$class->{$method}($id);
	}
	}
	$data=$class->getVariable();
	$this->reg=$class->getreg();
	unset($class);
	unset($model);
	//print_r($data);
	return $data;
	}	
	}
	
	
	}
	function loadModel($mname){
	$class=$mname."s";
	if(file_exists(MODEL.$class.".php")){
		include_once(MODEL.$class.".php");
		if(class_exists($class)){
		return new $class($this->db);
		}else{
			echo "class not found";
		}	
	}
	}
	function __autoload($class_name) {
   	try{
	include_once SYSTEM.$class_name.'.php';  
	}catch(Exception $e){
	echo "no file found".$e;
	}
	}
	function __destruct(){
	foreach(get_object_vars($this) as $k=>$v):
    unset($this->$k);
	endforeach;
	}
}
?>