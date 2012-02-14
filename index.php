<?php
session_start();
if (isset($_POST['password'])) {
	require_once 'includes/dbconnect.php';
	require_once 'includes/functions.php';
	
	$username = mysql_real_escape_string($_POST["username"]);
	$password = sha1($_POST["password"]. $salt);
	$login_qry = mysql_query("SELECT `id` FROM `user` WHERE `name` = '". $username. "' AND `password` = '". $password. "'");
	
	if ((mysql_num_rows($login_qry) == 1) or isset($_SESSION['loggedin_as'])) {
		$userid = mysql_fetch_assoc($login_qry);
		$_SESSION['loggedin_as'] = $userid["id"];
		if(is_mobile()){
			header('Location: m_all.php'); exit;  
		}else{
			header('Location: dashboard.php'); exit;  
		}
	} else {
		require_once 'includes/login_header.php';
		echo "<div style='text-align: center' class='wrongpw'>"._("Nutzer nicht gefunden oder Passwort falsch.")."</div>";
		require_once 'login.php';
		require_once 'includes/login_footer.php'; 
	}
} elseif(isset($_SESSION['loggedin_as'])) {
	if(isset($_REQUEST['mobile'])){
		header('Location: m_all.php'); exit;  
	}else{
		header('Location: dashboard.php'); exit;  
	}
} else {
	require_once 'includes/login_header.php';
	require_once 'login.php';
	require_once 'includes/login_footer.php'; 
}
?>
