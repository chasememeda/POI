<?php
	require_once("config.php"); 
	require_once("functions.php");
	if($_REQUEST["type"] == "combo"){
		$con = mysql_connect(SERVERNAME,USER,PWD);
		$db_selecct=mysql_select_db(DBNAME);
		mysql_query("set names utf8",$con); 
		
		if( $_POST["cattop"] > 0 && $_POST["cattop"] && "" != $_POST["cattop"]){
			$cattop = $_POST["cattop"];
			$sql = "SELECT * FROM poi_cat_sec WHERE top_cat_id = ". $_POST["cattop"]; 
			$result = mysql_query($sql,$con);
			while($row=mysql_fetch_assoc($result)){
					echo"<option value=".$row["id"].">".$row["NAME"]."</option>";
			}
			mysql_close($con);			
		}elseif($_POST["catsec"] && "" != $_POST["catsec"]){
			$catsec = $_POST["catsec"];
			$sql = "SELECT * FROM poi_cat WHERE sec_cat_id = ". $_POST["catsec"]; 
			$result = mysql_query($sql,$con);
			while($row=mysql_fetch_assoc($result)){
					echo"<option value=".$row["id"].">".$row["NAME"]."</option>";
			}
			mysql_close($con);			
		}
	}elseif($_REQUEST["type"] == "computing"){
		$con = mysql_connect(SERVERNAME,USER,PWD);
		$db_selecct=mysql_select_db(DBNAME);
		mysql_query("set names utf8",$con); 
		
		
		$cats = array();
		$cattop = $_POST["cattop"];
		$catsec = $_POST["catsec"];
		$cat	= $_POST["cat"];
		if( $cat > 0 ){
			array_push($cats,$cat);
		}else{
			if($catsec > 0 ){
				$sql = "SELECT id FROM poi_cat WHERE sec_cat_id = " . $catsec ;
				$result = mysql_query($sql,$con);
				while($row=mysql_fetch_assoc($result)){
					array_push($cats, $row["id"]);
				}
			}else{
				$sql = "SELECT a.ID as id
FROM poi_cat AS a
JOIN poi_cat_sec AS b ON a.sec_cat_id = b.id
WHERE b.top_cat_id = " . $cattop ;
				$result = mysql_query($sql,$con);
				while($row=mysql_fetch_assoc($result)){
					array_push($cats, $row["id"]);
				}
			}
		}
		
		$rslt = array("B001501C91","B00150205B","B001502066");
		$x = $_POST["x"];
		$y = $_POST["y"];
		$r = $_POST["distant"];
		if( $r < 0 ){
			//compute the shortest
			$shortest = NULL;
			$shortDis = -1;
			foreach( $cats as $c ){
				
				$kdt = new KDT($c,$con);
				if( NULL == $kdt->root ){
					// in case that under certain category, there are no facility
					continue;
				}
				$node = $kdt->nearest($x,$y);
				$dist = $node->distance($x,$y);
				
				if( NULL == $shortest ){
					$shortest = $node;
					$shortDis = $dist;
				}else{
					if( $dist < $shortDis){
						$shortDis = $dist;
						$shortest = $node;
					}
				}
			}
			if( NULL != $shortest ){
				$rslt = array($shortest->ID);
			}else{
				$rslt = array();
			}
		}else{
			//compute the available failities set
			$rslt = array();
			foreach( $cats as $c ){
				$kdt = new KDT($c,$con);
				if( NULL == $kdt->root ){
					// in case that under certain category, there are no facility
					continue;
				}
				$kdt->runRange($x,$y,$r);
				foreach( $kdt->cir->fSet as $node ){
					array_push( $rslt, $node->ID );
				}
			}
		}
		
		//echo _json_encode($rslt);
		//return;
		
		if( count($rslt) == 0 ){
			$rep = array();
		}else{
			$sql = "SELECT ID, NAME, X_COORD AS X, Y_COORD AS Y, CONCAT(ADDR_PROV , ' ' , ADDR_CITY , ' ' , ADDR_COUNTY , ' ' , ADDRESS) AS ADDR FROM poi_info WHERE ID IN (";
			$flag = false;
			foreach( $rslt as $k => $v){
				if($flag){
					$sql = $sql . ",";
				}
				$flag = true;
				$sql = $sql . "\"" . $v . "\"";
			}
			$sql = $sql . ");";
			$result = mysql_query($sql,$con);
			$rep = array();
			while($row=mysql_fetch_assoc($result)){
				array_push($rep, $row);
			}
			mysql_close($con);
		}
		echo _json_encode($rep);
	}
?>