<?php
session_start();
if (isset($_POST['password'])) {
	require_once('dbconnect.php');
	require_once('functions.inc.php');
	
	$username = mysql_real_escape_string($_POST["username"]);
	$password = sha1($_POST["password"]. $salt);
	$login_qry = mysql_query("SELECT `id` FROM `user` WHERE `name` = '". $username. "' AND `password` = '". $password. "'");
	
	if ((mysql_num_rows($login_qry) == 1) or isset($_SESSION['loggedin_as'])) {
		$userid = mysql_fetch_assoc($login_qry);
		$_SESSION['loggedin_as'] = $userid["id"];
		header('Location: '.(is_mobile() ? 'm_' : '').'all.php'); exit;            
	} else {
		require_once('headerl.php');
		echo "<div style='text-align: center' class='wrongpw'>"._("Nutzer nicht gefunden oder Passwort falsch.")."</div>";
		require_once('login.php');
		require_once('footl.php'); 
	}
} elseif(isset($_SESSION['loggedin_as'])) {
	header('Location: '.(isset($_REQUEST['mobile']) ? 'm_' : '').'all.php'); exit;  
} else {
	require_once('headerl.php');
	require_once('login.php');
	require_once('footl.php'); 
}
?>
