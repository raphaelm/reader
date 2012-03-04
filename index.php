<?php
require_once 'includes/dbconnect.php';
require_once 'includes/functions.php';
if (isset($_POST['password'])) {
	
	$username = mysql_real_escape_string($_POST["username"]);
	$password = sha1($_POST["password"].$salt);
	$login_qry = mysql_query("SELECT `id` FROM `user` WHERE `name` = '". $username. "' AND `password` = '". $password. "'");
	
	if (mysql_num_rows($login_qry) == 1) {
		$userid = mysql_fetch_assoc($login_qry);
		
		$session_key = sha1($userid.$_SERVER['REMOTE_ADDR'].microtime(1).mt_rand());
		
		if(isset($_POST['long'])){
			$expires = $expires_sql = time()+3600*24*30;
		}else{
			$expires_sql = time()+3600*24;
			$expires = null;
		}
		
		mysql_query('INSERT INTO sessions (session_key, user_id, expires) VALUES ("'.$session_key.'", '.intval($userid['id']).', '.$expires_sql.')');
		
		setcookie($cookiename, $session_key, $expires, null, null, null, true);
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
} elseif($user_id) {
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
