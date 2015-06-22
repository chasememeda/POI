<?php
require_once("tree-class.php");
function _json_encode($str){  
		$code = json_encode($str);  
		return preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $code);  
	}
function _json_decode($str , $type = false){  
 		$json = stripslashes($str); 
		return json_decode($json,$type);
	}

function eDistance($lat1, $long1, $lat2, $long2)
{
	$R_EARTH = 6370996.81;
	$pk = 180 / pi();
	$a1 = $lat1 / $pk;
	$a2 = $long1 / $pk;
	$b1 = $lat2 / $pk;
	$b2 = $long2 / $pk;
	$t1 = cos($a1) * cos($a2) * cos($b1) * cos($b2);
	$t2 = cos($a1) * sin($a2) * cos($b1) * sin($b2);
	$t3 = sin($a1) * sin($b1);
	$tt = acos($t1 + $t2 + $t3);
	return $R_EARTH * $tt;
}
?>