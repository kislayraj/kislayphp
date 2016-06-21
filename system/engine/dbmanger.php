<?php
 class dbmanger{
	protected $con=NULL;
	//Mysqli
	private $db=array('HOSTNAME'=>'localhost','USER'=>'root','PASS'=>'','DB'=>'database','driver'=>"mysqli");
	/*
	Mysql
	private $db=array('HOSTNAME'=>'localhost','USER'=>'root','PASS'=>'','DB'=>'skelvescrm','driver'=>"mysql");
	*/
	/*
	PDO
	private $db=array('HOSTNAME'=>'localhost','USER'=>'root','PASS'=>'','DB'=>'skelvescrm','driver'=>"pod");
	*/
	/*
	*/
	function getdb(){
		$this->db['driver']="db_".$this->db['driver'];
		include_once Db.$this->db['driver'].'.php';
	return new  $this->db['driver']($this->db);
	}
	function __autoload($class_name) {
	if(is_file(db.$class_name.'.php')){ include_once DB.$class_name.'.php'; } 	
	}
}


?>