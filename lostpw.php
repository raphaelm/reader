<?php
require_once('dbconnect.php');
require_once('headerl.php');

if(isset($_GET['hash'])){
	if($_GET['hash'] == sha1($_GET['user'].$secret."NEWPW".date("d.m.Y"))){
		$q = mysql_query('SELECT mail FROM user WHERE id = '.intval($_GET['user']));
		$me = mysql_fetch_object($q);
		$charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
		$autopw = "";
		for($i = 0; $i < 8; $i++){
			$autopw .= substr($charset, mt_rand(0, strlen($charset)-1), 1);
		}
		$m = mail($me->mail, '['.$title.'] Neues Passwort', sprintf('Hallo!
Hier ist dein neues Passwort für den %s!

%s

%s', $title, $autopw, $mainurl), 'From: '.$mailsender);
		echo "<div style='text-align: center' class='okay'>Du hast eine weitere E-Mail mit einem neuen Passwort!</div>";
		mysql_query("UPDATE user SET password = '".sha1($autopw. $salt)."' WHERE `id` = ". intval($_GET['user']));
		
		require_once('footl.php'); 
		exit;
	}else{
		echo "<div style='text-align: center' class='wrongpw'>Das ist leider schiefgegangen! War der Link schon abgelaufen?</div>";
		require_once('footl.php'); 
		exit;
	}
}

if($_POST['username']){
	$q = mysql_query('SELECT mail, id FROM user WHERE mail = "'.mysql_real_escape_string($_POST['username']).'" OR name = "'.mysql_real_escape_string($_POST['username']).'"');
	if(mysql_num_rows($q) == 1){
		$me = mysql_fetch_object($q);
		echo "<div style='text-align: center' class='okay'>Du hast per E-Mail weitere Anweisungen erhalten!</div>";
		mail($me->mail, '['.$title.'] Passwort vergessen?', sprintf('Hallo!
du hast im %s ein neues Passwort angefordert!
Wenn du das wirklich selbst warst, klicke auf untenstehenden Link. Wenn das
jemand anderes gewesen sein muss, brauchst du diese E-Mail nur zu ignorieren.
Achtung! Der Link gilt nur bis heute abend, 23:59!

%s', $title, $mainurl."lostpw.php?user=".$me->id."&hash=".sha1($me->id.$secret."NEWPW".date("d.m.Y"))), 'From: '.$mailsender);

	} else {
		echo "<div style='text-align: center' class='wrongpw'>Es tut uns leid, aber diesen Nutzer haben wir leider nicht im System!</div>";
	}
}
?>

<div id="header"></div>
<div id="content">
	<form id="login" action="lostpw.php" method="POST">
		<input class="inputl" type="text" name="username" value="Nutzername oder E-Mail" onfocus="if(this.value == 'Nutzername oder E-Mail') this.value = ''" onblur="if(this.value == '') this.value = 'Nutzername oder E-Mail'" />
		<input class="buttonl" type="submit" value="Abschicken" /> 
	</form>
	<a href="index.php" class="buttonl">Zurück</a>
	<div class="clear"></div>
</div>
<div id="footer">
	<p>geek's factory reader - on geeksfactory.de</p>
</div>
	
<?php
require_once('footl.php'); 
