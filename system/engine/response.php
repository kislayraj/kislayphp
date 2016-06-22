<?php
class response{
	private $data;
	private $re;
	private $reg;
	function __construct($action,$data,$reg=array()){
	$this->re=$action;	
	$this->data=$data;
	$this->reg=$reg;
	}
	function requestRender(){
	$helper=$this->reg_helper();
	if(!empty($this->data)){
	extract($this->data);
	}
	$file=Theme.$this->re['page']."/".$this->re['action'].".kis";
	if(!file_exists($file)){
	$file=Theme."common/error.kis";
	}
	if(!WS){
	include_once($file);
	}
	}
	function renderme($files){
	$helper=$this->reg_helper();
	//$sedata=$this->reg;
	if(!empty($this->reg)){
	foreach($this->reg as $sedata){
	!empty($sedata) ? extract($sedata) : '0';
	}
	}
	//$helper=$this->reg_helper();
		$file=explode('/',$files);
	$file=Theme.$file[0]."/".$file[1].".kis";
	if(!file_exists($file)){
	$file=Theme."common/error.kis";
	}
	include_once($file);
	}
	function reg_helper(){
	return new helper();
	}
}
?>
