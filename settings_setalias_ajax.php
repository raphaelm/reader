<?php
session_start();
require_once 'includes/dbconnect.php';
if (isset($_SESSION['loggedin_as']) and $_GET['hash'] == sha1($_SESSION['loggedin_as'].$salt)) {
	mysql_query("UPDATE feeds_subscription SET alias = '".htmlspecialchars(urldecode($_GET['alias']))."' WHERE userid = ".intval($_SESSION['loggedin_as'])." and feedid = ".intval($_GET['id']));
}
