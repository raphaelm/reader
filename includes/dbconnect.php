<?php
if(defined('DBCONNECT_INCLUDED'))
	return;
else
	define('DBCONNECT_INCLUDED', true);
	
$db_connection = false;
require_once 'config.inc.php';
mysql_connect($dbhostname, $dbusername, $dbpassword);
mysql_select_db($database);
$db_connection = true;

if(isset($_COOKIE[$cookiename])){
	$q = mysql_query('SELECT user_id FROM sessions WHERE session_key = "'.mysql_real_escape_string($_COOKIE[$cookiename]).'" and expires > '.time());
	if(mysql_num_rows($q) == 1){
		$r = mysql_fetch_object($q);
		$user_id = $r->user_id;
	}else{
		$user_id = false;
	}
}


require 'includes/setlocale.php';
