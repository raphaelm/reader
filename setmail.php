<?php
require_once 'includes/dbconnect.php';
$_GET['mail'] = urldecode($_GET['mail']);
if($_GET['hash'] == sha1($_GET['user'].$secret."CHANGEMAIL".$_GET['mail'])){
	mysql_query("UPDATE user SET mail = '".mysql_real_escape_string($_GET['mail'])."' WHERE `id` = ". intval($_GET['user']));
	header('Location: settings.php?mailchange=success');
	echo "OK";
}else die();
