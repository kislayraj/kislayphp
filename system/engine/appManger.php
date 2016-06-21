<?php
class appManger{
private $route;
private $action;
private $reg;
private $variable;
private $helper;
	function __construct(){
	$this->action=new action();
	$this->helper=new helper();		
	}
	function process(){
		$db=new dbmanger();
		$a=$this->action->getre();
	$request=new request($a,$db->getdb());
	$this->variable=$request->requestAction();
	$this->reg=$request->reg;
	unset($request);
	unset($db);
	}
	function render(){
	$a=$this->action->getre();
	$response=new response($a,$this->variable,$this->reg);
	$response->requestRender();
	unset($response);
	}
	function __destruct(){
	foreach(get_object_vars($this) as $k=>$v):
    unset($this->$k);
	endforeach;
	}
}
?>