<?php
class data{
	private $get;
	private $post;
	private $cookie;
	private $session;
	function __construct(){
	$this->get=$this->clean($_GET);
	$this->post=$this->clean($_POST);
	$this->session=$this->getsess();
	$this->cookie=$_COOKIE;
	}
	function get($key){
	return $key!=NULL ? $this->get[$key] : $this->get;
	}
	function post($key=NULL){
	
	return $key!=NULL ? $this->post[$key] : $this->post;
	}
	function cookie($key){
	return $key!=NULL ? $this->cookie[$key] : $this->cookie;
	}
	function session($key){
	return $key!=NULL ? $this->session[$key] : $this->session;
	}
	function clean($data)
	{
            foreach ($data as $key=>$value){
            $data[$key]=addslashes($value);
            }
            return $data;
	}
	function setsession($array){
		foreach($array as $key=>$value):
	$_SESSION[$key]=$value;
	endforeach;
	}
	function getsess(){
		session_start();
		return $_SESSION;
	}
	}
?>