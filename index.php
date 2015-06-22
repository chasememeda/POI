<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title> Assignment3 - Urban Computing </title>
<link rel="stylesheet" href="style.css" type="text/css">
<script type="application/javascript" src="js/jquery.js"></script>
<script charset="UTF-8" type="text/javascript" src="http://dev.ditu.live.com/mapcontrol/mapcontrol.ashx?v=6.2&mkt=en-us">
</script>
</head>

<body>
	<?php
		header("Content-Type: text/html; charset=utf-8");
		require_once("config.php");
		require_once("functions.php");
		$servername = SERVERNAME ;
		$username = USER;
		$pwd = PWD;
		$db_database = DBNAME;
		//check connection to db
		$con = mysql_connect($servername,$username,$pwd);
		mysql_query("set names utf8",$con); 
		if (!$con)
		  {
		  die('ERROR : Could not connect to the db : ' . mysql_error());
		  }
		$db_selecct=mysql_select_db($db_database);
		if(!$db_selecct)
		{
			die('ERROR : Could not connect to the db : '.mysql_error());	
		}
    ?>
    <div class="wrap">
      <div class="chose_box">
      	<div class = "start_box">
                <input type="submit" name="button" id="start" value="SUBMIT">
                <p id="start_label">computing...</p>
            </div>
        <div class = "chose">
        	<p style="
                font-style: italic;
            ">Please chose the service that you want:</p>
          <h3>Computing type:</h3>
          <div class="computing_type">
            <p>
              <label class="radio">
                <input name="computingtype" type="radio" value="0" checked="checked">
                Closest available facility</label>
            </p>
            <p><label class="radio">
                <input name="computingtype" type="radio" value="1" >
                All available facilities within</label> <input id="dist_value" name="dist" type="text" value="1000" disabled="disabled">m</p>
        </div>
          <h3>Facility type:</h3>
          <div class="facility_type">
          <?php
          	//cat level top
			$sql = "SELECT * FROM poi_cat_top"; 
			$result = mysql_query($sql,$con);
		  ?>
          <select name="cat1" class="cat">
          	<?php
				while($row=mysql_fetch_assoc($result)){
					echo"<option value=".$row["ID"].">".$row["NAME"]."</option>";
				}
			?>
          </select>
          <select name="cat2"  class="cat"><option value = "0">所有</option>
          <?php 
		  		$sql = "SELECT * FROM poi_cat_sec WHERE top_cat_id = 1"; 
				$result = mysql_query($sql,$con);
				while($row=mysql_fetch_assoc($result)){
					echo"<option value=".$row["id"].">".$row["NAME"]."</option>";
				}
				?>
          </select>
          <select name="cat3" ><option value = "0">所有</option>
          </select></div>
          </div>
          <h3>MAP and result:</h3>          
          <div class="rsult_list"><input name="locationSelect" locate = "false" type="button" value="Select location on the map" /><div>Your location: <span id="info"></span> </div>
          <div>Facility list:</div>
          <ul id = "rslt">
          </ul>
          </div>
          <div id='myMap'></div>
        </div>
    </div>
    <script type="application/javascript" src="js/map_script.js"></script>
    <script type="application/javascript" src="js/script.js"></script>
    <?php
	/*
		$kdt = new KDT(10000,$con);
		echo "<pre>";
		
		//$node = $kdt->arr["B00155QP11"];
		$kdt->runRange(121.413 , 31.031 , 1000);
		//print_r($kdt);
		//print_r($node);
		//echo $node->distance( 121.413,31.031 );
		print_r($kdt->cir->fSet);*/
	?>
</body>
</html>

