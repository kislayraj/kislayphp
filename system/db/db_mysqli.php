<?php
class db_mysqli extends sql{
	protected $con=NULL;
	private $db;
	private $details=array();
	function __construct($array){
	$this->details=$array;
	}
	function connect(){
		if($this->con==NULL  ){
			$this->con= new mysqli($this->details['HOSTNAME'], $this->details['USER'],$this->details['PASS'],$this->details['DB']);
			if ($this->con->connect_error) {
    			die('Error : ('. $this->con->connect_errno .') '. $this->con->connect_error);
			}
		}
	}
	function getNums(){
	$query=$this->toquery();
	$result=$this->con->query($query);
	$nums=$result->num_rows($sql);
	return $nums;
	}
	function query($s=array()){
	$query=$this->toquery();
	$sql=$this->con->query($query);
	if($sql){
		return array("code"=>200,"insertId"=>"");
		}
	else{
		return array("code"=>400,"error"=>"");
		}
	}
	function fetch_array(){
	$query=$this->toquery();
	$sql=$this->con->query($query);
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
	$row=$this->con->query($query);
	return $row->fetch_assoc();
	}
	function close(){

	}
	function __destruct(){
	
	foreach(get_object_vars($this) as $k=>$v):
    unset($this->$k);
	endforeach;
	}
}
?>
