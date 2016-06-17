<?php
class action{
	private $re;	
	public function __construct($string=NULL){	
	$array=$string==NULL ? $_GET : $this->getroute($string);
	$this->re['action']=!empty($array['action']) ? $array['action'] : 'index';
	$this->re['page']=!empty($array['page']) ? $array['page'] : 'common';
	$this->re['id']=!empty($array['id']) ? $array['id'] : NULL;
	unset($array);
	}
	function getroute($string){
	$arry=explode("/",$string);
	foreach($arry as $key=>$value){
	if($value==NULL){
	unset($arry[$key]);
	}
	}
	if(count($arry)==3){
	return array('page'=>$arry[0],'action'=>$arry[1],'id'=>$arry[2]);
	}elseif(count($arry)==2){
	return array('page'=>$arry[0],'action'=>$arry[1],'key'=>NULL);
	}
	elseif(count($arry)==1){
	return array('page'=>$arry[0],'action'=>'index','key'=>NULL);
	}
	}
	function getre(){
	return $this->re;
	}
	}
	function __destruct(){
	foreach(get_object_vars($this) as $k=>$v):
    unset($this->$k);
	endforeach;
	}
?>