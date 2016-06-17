<?php
class ajax{
	function __construct(){
	}
	function start(){
	echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>';
	echo "<script> \n"; 
	echo "function callme(files,name,div){ \n";
	echo "$.ajax({ \n";
	echo "url: files+\"?class=\"+name, \n";
	echo "success: function(data){ \n";
		echo "$(div).html(data); \n";
		echo "}, \n";
		echo "beforeSend: function(data) { \n";
		echo "	$(div).html(\"...\");";
		echo "}\n";
	echo "}); \n";
	echo "return false; \n";
	echo "} \n";
	echo "</script> \n"; 
	}
	function call($file,$class,$id){
	echo "return callme('$file','$class','$id')";
	}

}
?>