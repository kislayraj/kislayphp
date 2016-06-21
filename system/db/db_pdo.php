<?php
class db_pdo extends sql{
	protected $con=NULL;
	private $db;
	private $details=array();
	function __construct($array){
	$this->details=$array;
	}
	function connect(){
		if($this->con==NULL  ){
			$this->con= new PDO('mysql:host='.$this->details['HOSTNAME'].';dbname='.$this->details['DB'].';charset=utf8mb4',$this->details['USER'],$this->details['PASS'], array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			
		}
	}
	function getNums(){
	$query=$this->toquery();
	$result=$this->con->exec($query);
	$nums=$result->lastInsertId();
	return $nums;
	}
	function query($s=array()){
	$query=$this->toquery();
	extract($s);
	$sql=$this->con->exec($query);
	if($sql){
		return array("code"=>200,"insertId"=>$this->con->lastInsertId());
		}
	else{
		return array("code"=>400,"error"=>"");
		}
	}
	function fetch_array(){
	$query=$this->toquery();
	$sql=$this->con->query($query);
	$data=array();
	while($row=$sql->fetch(PDO::FETCH_ASSOC)){
		$data[]=$row;
		}	
	return $data;
	}
	
	function fetch_row(){
	$query=$this->toquery();
	$row=$this->con->query($query);
	return $rowfetch(PDO::FETCH_ASSOC);
	}
	function close(){
	if($this->con){
		//$this->con->close();
	//return mysql_close($this->con);
	}
	}
	function __destruct(){
		//$this->con->close();
	foreach(get_object_vars($this) as $k=>$v):
    unset($this->$k);
	endforeach;
	}
}
?>