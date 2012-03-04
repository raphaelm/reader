<?php
require_once 'includes/dbconnect.php';
mysql_query('DELETE FROM sessions WHERE session_key = "'.mysql_real_escape_string($_COOKIE[$cookiename]).'"');
setcookie($cookiename, false, time()-3600);
header('Location: index.php'.(isset($_REQUEST['mobile']) ? '?mobile=true' : '').''); exit;   
