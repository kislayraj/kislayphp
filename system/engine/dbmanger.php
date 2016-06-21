<?php
 class dbmanger{
	protected $con=NULL;
	private $db=array('HOSTNAME'=>'localhost','USER'=>'root','PASS'=>'','DB'=>'skelvescrm','driver'=>"dmysqli");
	function getdb(){
		include_once Db.$this->db['driver'].'.php';
	return new  $this->db['driver']($this->db);
	}
	function __autoload($class_name) {
	if(is_file(db.$class_name.'.php')){ include_once DB.$class_name.'.php'; } 	
	}
}


?>
