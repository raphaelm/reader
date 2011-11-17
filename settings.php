<?php
session_start();
if (isset($_SESSION['loggedin_as'])) {
	require_once("dbconnect.php"); 
	require_once("functions.inc.php");
	include('headeri.php');
	include('navi.inc.php');
	
	echo '<div id="right-col">
		  <div id="wrap"><h2>Einstellungen</h2>';
	
	function add_feed($url){
		$url = fetch_feedurl(trim($url));
		$feedname = fetch_feedtitle($url);
		if($feedname == '') $feedname = htmlspecialchars($url);
		mysql_query("INSERT IGNORE INTO `feeds` (`name`, `url`) VALUES ('". mysql_real_escape_string($feedname). "', '". mysql_real_escape_string($url)."')");
		mysql_query("INSERT IGNORE INTO `feeds_subscription` (`feedid`, `userid`, `lastupdate`) VALUES ((SELECT `id` FROM `feeds` WHERE `url` = '". mysql_real_escape_string($url)."', ".time.time()."), '". $_SESSION['loggedin_as']. "')");
	}
	
	if(isset($_GET['mailchange']) and $_GET['mailchange'] == 'success')
			echo '<p class="okay">Deine E-Mail-Adresse wurde erfolgreich geändert.</p>';
	
	if (isset($_POST['feedurl']) && !empty($_POST['feedurl'])) {
		if(mysql_num_rows(mysql_query('SELECT * FROM feeds_subscription a WHERE a.userid = '.$_SESSION['loggedin_as'].' and 1 = (SELECT COUNT(*) FROM feeds f WHERE f.id = a.feedid AND f.url = "'.mysql_real_escape_string($_POST['feedurl']).'")')) == 0){
			echo mysql_error();
			$add = add_feed($_POST['feedurl']);
			echo '<p class="okay">Dein Feed wurde erfolgreich hinzugefügt. Nach dem nächsten Feed-Update (in spätestens 5 Minuten) wird dann links in der Leiste auch sein korrekter Titel angezeigt.</p>';
		}else
			echo '<p class="error">Du hast diesen Feed bereits abonniert.</p>';
	}
	if (!empty($_GET["del"])) {
		mysql_query("DELETE FROM `feeds_subscription` WHERE `feedid` ='". intval($_GET["del"]). "' AND `userid` =". $_SESSION['loggedin_as']);
		$abo_del_qry = mysql_query("SELECT 0 FROM `feeds_subscription` WHERE `feedid` = ". intval(($_GET["del"]))); 
		if (mysql_num_rows($abo_del_qry) == 0) {
			mysql_query("DELETE FROM `feeds_entries` WHERE feed_id = '". intval($_GET["del"]). "'"); 
			mysql_query("DELETE FROM `feeds` WHERE id = '". intval($_GET["del"]). "'");   
		}
		echo '<p class="okay">Dein Abonnement des Feeds wurde erfolgreich entfernt.</p>';
	}
	
	$meq = mysql_query('SELECT * FROM user WHERE id = '.intval($_SESSION['loggedin_as']));
	$me = mysql_fetch_object($meq);
	
	if(isset($_POST['oldpw'])){
		$password = sha1($_POST["oldpw"]. $salt);
		$login_qry = mysql_query("SELECT `id` FROM `user` WHERE `id` = ". $_SESSION['loggedin_as']. " AND `password` = '". $password. "'");
		if (mysql_num_rows($login_qry) == 1) {
			if(isset($_POST['newpw']) and !empty($_POST['newpw'])){
				if($_POST['newpw'] != $_POST['newpw2'])
					echo '<p class="error">
						Das neue Passwort muss zweimal gleich eingegeben werden.
					</p>';
				else {
					mysql_query("UPDATE user SET password = '".sha1($_POST["newpw"]. $salt)."' WHERE `id` = ". $_SESSION['loggedin_as']);
					echo '<p class="okay">
						Das Passwort wurde geändert.
					</p>';
				}
			}
			if($_POST['mail'] != $me->mail){
				if(preg_match('/^[a-z0-9.\-+_]+@[a-z0-9.\-+_]+\.[a-z]+$/i', $_POST['mail']) == 0)
					echo '<p class="error">
						„'.htmlspecialchars($_POST['mail']).'“ ist keine gültige E-Mail-Adresse. Wenn du das anders siehst, kontaktiere uns bitte.
					</p>';
				else{
					mail($_POST['mail'], '['.$title.'] Bestätigung der Änderung der E-Mail-Adresse', sprintf('Hallo!
jemand möchte seine im %s angegebene E-Mailadresse zu dieser ändern.
Wenn du das warst, klicke bitte untenstehenden Link an. Wenn nicht,
ignoriere diese E-Mail einfach.

%s', $title, $mainurl."setmail.php?user=".$me->id."&mail=".urlencode($_POST['mail'])."&hash=".sha1($me->id.$secret."CHANGEMAIL".$_POST['mail'])), 'From: '.$mailsender);
					echo '<p class="okay">
						Es wurde eine Bestätigung zur Änderung der Mailadresse an '.htmlspecialchars($_POST['mail']).' versandt.
					</p>';
				}
			}
		} else {
			echo '<p class="error">
				Das aktuelle Passwort wurde nicht korrekt eingegeben!
			</p>';
		}
	}
	
	echo '<h3>Persönliche Einstellungen</h3>';
	
	echo '<form action="settings.php" method="post">
		<table>
			<tr>
				<td valign="top">Aktuelles Passwort:</td>
				<td><input type="password" name="oldpw" value="" id="" /></td>
			</tr>
			<tr>
				<td valign="top">Neues Passwort:</td>
				<td><input type="password" name="newpw" value="" id="" /></td>
			</tr>
			<tr>
				<td valign="top">Neues Passwort wiederholen:</td>
				<td><input type="password" name="newpw2" value="" id="" /></td>
			</tr>
			<tr>
				<td valign="top">E-Mail-Adresse:</td>
				<td><input type="text" name="mail" value="'.$me->mail.'" id="" />
				<p class="info"><small>Nicht wundern: Nachdem du deine E-Mail-Adresse geändert hast, wird hier weiterhin deine alte Adresse stehen, <br />bis du einen Link in einer E-Mail, die wir an deine neue Adresse senden, angeklickt hast!</small></p></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" value="Speichern" /></td>
			</tr>
		</table>
	</form>';
	?>
	<h3>Feed abonnieren</h3>
	<form id="addsub" action="settings.php" method="POST">
	  <input type="text" name="feedurl" value="http://" />
	  <input type="submit" value="Abonieren" /> 
	</form>
	<?php
	echo '<h3>Abos</h3>';
	$feeds_qry = mysql_query("SELECT `feedid`, `feedname`, `alias`, `origname`, `feedurl`, `lastupdate` FROM `view_feed_subscriptions` WHERE `userid` =". $_SESSION['loggedin_as']. " AND feedid > 0 ORDER by `feedname` asc");
	if(mysql_num_rows($feeds_qry) == 0){
		echo "<p>Keine Feeds gefunden.</p>";
	} else {
		echo '<table border="0">
				<tr>
				  <th>Name</th>
				  <th>Alias</th>
				  <th>Löschen</th>
				</tr>';
		while ($row = mysql_fetch_assoc($feeds_qry)) {
			echo '<tr><td id="title_'.$row["feedid"].'">';
			if(time()-$row["lastupdate"] > 1000){
				echo '<img src="images/error.png" class="erroricon" alt="Fehler" title="Dieser Feed konnte kürzlich nicht erfolgreich abgerufen werden." /> ';
			}
			echo '<a href="'.$row['feedurl'].'" class="feedlink" target="_blank">'.utf_correct($row["origname"]). '</a></td><td id="alias_'.$row["feedid"].'">'. utf_correct($row["alias"]). '</td><td><a href="settings.php?del='. $row["feedid"]. '">Löschen</a> | <a href="javascript:editalias('.$row["feedid"].')" id="editaliaslink_'.$row["feedid"].'">Alias setzen</a></td></tr>';
		}
		echo '</table>';
	}   
	
	?>
		  
	<div id="desknot" style="display:none;">
		<h3>Desktop Notifications</h3>
		<p class="info">
			Nicht wundern: Einstellung wird nur für diesen Computer gespeichert (als Cookie).
		</p>
		<p><a href="javascript:void(0)" id="actnot">Desktop Notifications aktivieren</a>
		<a href="javascript:void(0)" id="deanot">Desktop Notifications deaktivieren</a></p>
	</div>	  
	<script type="text/javascript">
		if (window.webkitNotifications) {
			if(readCookie('desknot') == 'true'){
				$("#actnot").hide();
			}else{
				$("#deanot").hide();
			}
			$("#desknot").show();
		}
		$("#actnot").bind("click", function() {
			if (window.webkitNotifications.checkPermission() == 0) { // 0 is PERMISSION_ALLOWED
				n = window.webkitNotifications.createNotification('images/gfr.png', 'Test', 'erfolgreich');
				n.show();
				createCookie('desknot', 'true', 365*5);
				$("#deanot").show();
				$("#actnot").hide();
			} else {
				window.webkitNotifications.requestPermission();
			}
		});
		$("#deanot").bind("click", function() {
			createCookie('desknot', 'false', 365*5);
			$("#deanot").hide();
			$("#actnot").show();
		});
		
		function editalias(id){
			alias = $("#alias_"+id).html();
			if(alias == '') alias = $("#title_"+id+" a").html();
			$("#alias_"+id).html('<form action="" id="editalias_'+id+'"><input type="text" class="alias" value="'+alias+'" /><input type="submit" value="Speichern" /></form>');
			$("#editaliaslink_"+id).hide();
			$("#editalias_"+id).bind('submit', function(){
				newa = $("#alias_"+id+" input.alias").val();
				$.get('settings_setalias_ajax.php?hash=<?php echo sha1($_SESSION['loggedin_as'].$salt); ?>&id='+id+'&alias='+escape(newa), function(){
						$("#alias_"+id).html(newa);
						$("#editaliaslink_"+id).fadeIn();
					});
				return false;
			});
		}
	</script>
	
	</div></div>
	  <div id="right-gap"></div>
	  <div class="clear"></div>
	<?php
	include('footl.php');
} else {
	header('Location: index.php'); exit;       
}
?>                                           
