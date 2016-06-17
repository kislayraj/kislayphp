<?php

class Model{
	public $db=NULL;
	function __construct($db){
	$this->db=$db;
	
	}
	function find($table=NULL,$content='*',$condition=array(),$mess=array()){
	$table=$table==NULL ? $this->name : $table;
	$this->db->clear();
	$this->db->select($table,$content);
	foreach($condition as $key=>$value){
	$this->db->{$key}($value);
	}	
	
	return $this->db->fetch_array();	
	}
	function get($table=NULL,$content='*',$condition=array(),$mess=array()){
	$table=$table==NULL ? $this->name : $table;
	$this->db->clear();
	$this->db->select($table,$content);
	foreach($condition as $key=>$value){
	$this->db->{$key}($value);
	}
	return $this->db->fetch_row();
	}
	function add($table,$data){
	$this->db->clear();
	$this->db->insert($table,$data);
	return $this->db->query();
	}
	function update($table,$data,$where,$mess=array('Sucess','Fail')){
	$this->db->clear();
	$this->db->update($table,$data);
	$this->db->where($where);
	return $this->db->query();	
	}
	function delete($table,$where,$mess=array('Sucess','Fail')){
	$this->db->clear();
	$this->db->delete($table);
	$this->db->where($where);
	return $this->db->query();
	}
	function csql(){
	return	 $this->db->test();
	}
	

}

?>