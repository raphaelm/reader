<?php
if(defined('DBCONNECT_INCLUDED'))
	return;
else
	define('DBCONNECT_INCLUDED', true);
	
$db_connection = false;
require_once 'config.inc.php';
mysql_connect($dbhostname, $dbusername, $dbpassword);
mysql_select_db($database);

require 'includes/setlocale.php';
