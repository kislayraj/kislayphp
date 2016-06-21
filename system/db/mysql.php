<?php
abstract  class sql {
private $sql;
private $where;
private $order;
private $limit;
private $query;
	function select($table,$content='*'){
	if($this->sql==NULL){
	$this->sql="select ".$content." from ".$table;
	}
	}
	function test(){
	
	return $this->toquery();
	}
	function delete($table){
	if($this->sql==NULL){
	$this->sql="delete  from ".$table;
	}
	}
	function update($table,$data){
	$str=array();
	foreach(array_keys($data) as $key){
	$str[]=$key."='".$data[$key]."' ";
	}
	if($this->sql==NULL){
	$this->sql="update ".$table." set ".implode(',',$str);
	}
	}
	function insert($table,$data){
	$key=implode(',',array_keys($data));
	$values=implode('\',\'',$data);
	if($this->sql==NULL){
	$this->sql="Insert into ".$table."(".$key.") values('".$values."')";
	}
	}
	function where($where){	
	if($this->where==NULL){
	$this->where=" where ".$where." ";
	}else{
	$this->where.=" and ".$where." ";
	}	
	}
	function order($order){
	if($this->order==NULL){
	$this->order=" order by ".$order;
	}
	}
	function limit($limit){
	if($this->limit==NULL){
	$this->limit=" limit ".$limit;
	}
	}
	function toquery(){
	$this->connect();
	if($this->query==NULL){
	$this->query=$this->sql.$this->where.$this->order.$this->limit;
	}
	return $this->query ;
	}
	function clear(){	
	$this->sql=NULL;
	$this->where=NULL;
	$this->limit=NULL;
	$this->order=NULL;
	$this->query=NULL;
	}
	function setquery($sql){
	$this->query=$sql;
	return true;
	}
	
}
class mysql extends sql{
	protected $con=NULL;
	private $db;
	private $de=array();
	function __construct($array){
	$this->de=$array;
	}
	function connect(){

		if($this->con==NULL  ){
		$conection= mysql_connect($this->de['HOSTNAME'], $this->de['USER'],$this->de['PASS'])or die('Con\'t connect to database Please Configure your database first');
		if($conection){
			$this->con=$conection;
			}
			else{
			trigger_error('Con\'t Connect to DataBAse');
			}
			
		if($this->con!=NULL){
			$this->de=mysql_select_db($this->de['DB'],$this->con) or die('Please Make sure the database is exit or rename it');
		}
		}
	}
	function getNums(){
	$query=$this->toquery();
	$sql=mysql_query($query,$this->con);
	$nums=mysql_num_rows($sql);
	return $nums;
	}
	function query($s=array('sucess'=>'Query has been Sucessed','fail'=>'Query has been Failed ')){
	$query=$this->toquery();
	extract($s);
	$sql=mysql_query($query);
	if($sql){
		return $sucess;
		}
	else{
		return $fail." ".mysql_error();
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
