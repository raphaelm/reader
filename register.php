<?php
require_once('includes/login_header.php');

if (!empty($_POST['regusername']) && !empty($_POST['regpassword']) && !empty($_POST['regmail'])) {
	require_once 'includes/dbconnect.php';
	$username = mysql_real_escape_string($_POST["regusername"]);
	$password = sha1($_POST["regpassword"]. $salt);
	$mail = mysql_real_escape_string($_POST['regmail']);
	$reg_qry = mysql_query("INSERT INTO `user` (`name`, `password`, `mail`, `locale`) VALUES ('". $username. "', '". $password."', '". $mail."', '".$locale."')");
	if(mysql_num_rows(mysql_query("SELECT * FROM feeds WHERE id = 0")) == 1){
		mysql_query("INSERT INTO feeds_subscription (feedid, userid) VALUES (0, ".mysql_insert_id().")");
	}
	if ($reg_qry != false) {
		echo "<p class='okay'>"._("Registrierung erfolgreich!")." <a href='/'>"._("Zur Hauptseite")."</a></p>";
	} else {
		echo "<p class='error'>"._("Registrierung fehlgeschlagen! Eventuell ist dieser Nutzername bereits vergeben!")." <a href='javascript:history.back()'>"._("Zurück")."</a></p>";
	}
}
if (!empty($_POST['website'])) {
	echo '<div id="content"><form id="login" action="register.php" method="POST">
		<input class="inputl" type="text" name="regusername" placeholder="'._('Nutzername').'" />
		<input class="inputl" type="password" name="regpassword" placeholder="'._('Passwort').'" />
		<input class="inputl" type="text" name="regmail" placeholder="'._('name@domain.tld').'" />
		<input class="inputl" type="text" name="website" value="" style="display:none;"/>
		<input class="buttonl" type="submit" value="'._('Registrieren.').'" /> 
	</form></div>';      
}else{
	echo '<div id="header"></div><div id="content">
		<h2>'._('Registrieren').'</h2>
		<form id="login" action="register.php" method="POST">
		  <input class="inputl" type="text" name="regusername" placeholder="'._('Nutzername').'" />
		  <input class="inputl" type="password" name="regpassword" placeholder="'._('Passwort').'" />
		  <input class="inputl" type="text" name="regmail" placeholder="'._('name@domain.tld').'" />
		  <input class="inputl" type="text" name="website" placeholder="" style="display:none;"/>
		  <input class="buttonl" type="submit" value="'._('Registrieren').'" /> 
		</form>
	  <a href="index.php" class="buttonl">'._('Abbrechen').'</a>
	  <div class="clear"></div></div>';
}

require_once('includes/login_footer.php'); 

