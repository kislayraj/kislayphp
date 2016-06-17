<?php
class html {
	private $tags;
	function gettag($tag,$array=NULL){
	$this->tags=NULL;
	$element='';
	$main='';
	if($array!=NULL){
	foreach($array as $key=>$value){
	$element.=$key!='main' ? " ".$key."=\"".$value."\" " : '';
	$main.=$key=='main' ? $value :'';
	}
	}
	$this->tags=!in_array('main',array_keys($array)) ? "<".$tag.$element." /> \n" : "<".$tag.$element." >".$main."</".$tag.">\n";
	echo $this->tags;
	}
	function start($tag,$array=NULL){
	$this->tags=NULL;
	$element='';
	if($array!=NULL){
	foreach($array as $key=>$value){
	$element.=" ".$key."=\"".$value."\" ";
	}
	}
	$this->tags="<".$tag.$element.">\n";
	echo $this->tags;
	}
	function end($tag){
	$this->tags="</".$tag." >\n";
	echo $this->tags;
	}
}
?>