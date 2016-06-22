<?php
class output{
	function json($str){
		header("Content-Type:application/json");
		if(is_array($str)){
			echo json_encode($str);
		}else{
			echo $str;
		}
		//exit();
	}
	function xml($str,$f="add",$s="item"){
		header("Content-Type:text/xml");
		if(is_array($str)){
		$xml=$f!="xml" ? '<?xml version="1.0" encoding="utf-8"?>' : "";
		$xml.=$this->xmlFormater($str,$f,$s);
		echo $xml;
		}else{
		echo $str;
		}
		//exit();
	}
	function xmlFormater($arr,$f="add",$s="item"){
	$xml="<$f>";
	if(is_array($arr)){
		foreach($arr as $k=>$v){
			if(!is_numeric($k)){
			$xml.="<$k>";
			$ender="</$k>";
			}
			if(is_array($v)){
			$xml.=$this->xmlFormater($v,$s);
			}else{
			$xml.=$v;
			}
			if(isset($ender)):
			$xml.=$ender;
			endif;
		}
	}
	$xml.="</$f>";
	return $xml;
	}
}
?>