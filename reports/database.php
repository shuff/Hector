<?php
$sqllogin = 'the_duke';
$sqlpassword = 'oudonotknow';
$host = 'localhost';
$db = 'hec';	

$con = mysql_connect($host,$sqllogin,$sqlpassword);
	
if (!$con){
		die('Could not connect: ' . mysql_error());
}		
mysql_select_db($db, $con);

?>