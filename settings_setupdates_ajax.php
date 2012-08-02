<?php
require_once 'includes/dbconnect.php';
if ($user_id and $_GET['hash'] == sha1($user_id.$salt.date('Ymd'))) {
	mysql_query("UPDATE feeds_subscription SET `updates` = NOT `updates` WHERE userid = ".intval($user_id)." and feedid = ".intval($_GET['id']));
}
