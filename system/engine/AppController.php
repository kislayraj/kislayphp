<?php
class AppController{
		protected $data;
		protected $reg;
		protected $model;
		protected $helper=array();
		private $variable=array();
		private $call;
		function __construct($model,$data,$call){
		$this->data=$data;
		$this->model=$model;
		$this->call=$call;		
		}
		function __get($key){
		if(in_array($key,$this->helper)):
		return $this->helper[$key];
		endif;
		}
		function __call($method,$arg=array()){
			try{
				return $this->call->{$method}($arg[0]);
			}catch(Exception $e){
				echo "No Such Method Found";
			}
		}
		function __set($key,$value){
		$this->helper[$key]=$value;
		}
		function converter($data,$string,$sprate=','){
		$array=explode($sprate,$string);
		$return=array();
		foreach($array as $d){
		$return[$d]=$data[$d];
		}
		return $return;
		}
		function isAuthorised($method=NULL,$url=NULL){
		$method!=NULL ? "post" : $method;
		$kis=false;
		if($url==NULL){ $kis=isset($this->data->{$method("developer")}) ? true : false; }
		else{ $kis=$_SERVER['HTTP_REFERER']==$url ? true : false; }
		return $kis;
		}
		function val($string,$in,$sprate=','){
			$val=explode($sprate,$string);
			$return=array();
			foreach($val as $d){
			if($in[$d]==""){
			$return[]=$val." can't be Empty";
			}
			}
			return $return;
		}
		function setreg($key,$value){
		$this->reg[$key]=$value;	
		}
		function getreg(){
		return $this->reg;
		}
		function isdata($in,$what){
		return $this->data->$in==$what ? true : false;
		}	
		function set($key,$value){
		$this->variable[$key]=$value;
		}
		function get($key){
		if(in_array($key,$this->varible)):
		return $this->variable[$key];
		endif;
		}
		function getVariable(){
		return $this->variable;
		}
		function register($class){
		//echo $_SERVER['REQUEST_URI'];
		if(is_file(plugin.$class.'.php')){
			include_once(plugin.$class.'.php');
			if(class_exists($class)){	
			return new $class();
			}
			}else{
			echo plugin.$class.'.php';
			}
		}
		function __destruct(){
	foreach(get_object_vars($this) as $k=>$v):
    unset($this->$k);
	endforeach;
	
	}
}
?>