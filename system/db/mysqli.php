<?php
class mysql extends sql{
	protected $con=NULL;
	private $db;
	private $details=array();
	function __construct($array){
	$this->details=$array;
	}
	function connect(){
		if($this->con==NULL  ){
			$this->con= new mysqli($this->de['HOSTNAME'], $this->de['USER'],$this->de['PASS'],$this->de['DB']);
			if ($mysqli->connect_error) {
    			die('Error : ('. $this->con->connect_errno .') '. $this->con->connect_error);
			}
		}
	}
	function getNums(){
	$query=$this->toquery();
	$result=$this->con->query($query,$this->con);
	$nums=$result->num_rows($sql);
	return $nums;
	}
	function query($s=array('sucess'=>'Query has been Sucessed','fail'=>'Query has been Failed ')){
	$query=$this->toquery();
	extract($s);
	$sql=$this->con->query($query);
	if($sql){
		return $sucess;
		}
	else{
		return $fail." ".mysql_error();
		}
	}
	function fetch_array(){
	$query=$this->toquery();
	$sql=$this->con->query($query,$this->con);
	$data=array();
	while($row=$sql->fetch_assoc()){
		$data[]=$row;
		}	
		$sql->free();
// close connection 

	return $data;
	}
	
	function fetch_row(){
	$query=$this->toquery();
	$row=$this->con->query($query,$this->con);
	return $row;
	}
	function close(){
	if($this->con){
		$this->con->close();
	//return mysql_close($this->con);
	}
	}
	function __destruct(){
		$this->con->close();
	foreach(get_object_vars($this) as $k=>$v):
    unset($this->$k);
	endforeach;
	}
}
?>