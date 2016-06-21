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
?>