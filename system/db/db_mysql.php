<?php
class db_mysql extends sql{
	protected $con=NULL;
	private $db;
	private $de=array();
	function __construct($array){
	$this->details=$array;
	}
	function connect(){
		if($this->con==NULL  ){
		$conection= mysql_connect($this->details['HOSTNAME'], $this->details['USER'],$this->details['PASS'])or die('Con\'t connect to database Please Configure your database first');
		if($conection){
			$this->con=$conection;
			}
			else{
			trigger_error('Con\'t Connect to DataBAse');
			}
			
		if($this->con!=NULL){
			$this->de=mysql_select_db($this->details['DB'],$this->con) or die('Please Make sure the database is exit or rename it');
		}
		}
	}
	function getNums(){
	$query=$this->toquery();
	$sql=mysql_query($query,$this->con);
	$nums=mysql_num_rows($sql);
	return $nums;
	}
	function query($s=array()){
	$query=$this->toquery();
	extract($s);
	$sql=mysql_query($query);
	if($sql){
		return array("code"=>200,"insertId"=>mysql_insert_id());
		}
	else{
		return array("code"=>400,"error"=>mysql_error());
		}
	}
	function fetch_array(){
	$query=$this->toquery();
	$sql=mysql_query($query,$this->con);
	$data=array();
	while($row=mysql_fetch_array($sql)){
		$data[]=$row;
		}	
	return $data;
	}
	
	function fetch_row(){
	$query=$this->toquery();
	$sql=mysql_query($query,$this->con);
	$row=mysql_fetch_assoc($sql);
	return $row;
	}
	function close(){
	if($this->con){
	//return mysql_close($this->con);
	}
	}
	function __destruct(){
	foreach(get_object_vars($this) as $k=>$v):
    unset($this->$k);
	endforeach;
	}
	
	
}

?>