<?php
require_once 'includes/dbconnect.php';
session_start();
if ($user_id) {
	require_once 'includes/functions.php';
	require 'includes/application_header.php';
	require 'includes/application_navi.php';
	
	echo '<div id="right-col">
		  <div id="wrap" class="settingspage"><h2>'._('Einstellungen').'</h2>';
	
	function add_feed($url){
		global $user_id;
		$url = fetch_feedurl(trim($url));
		if($url === -7){
			return -7;
		}
		$feedname = fetch_feedtitle($url);
		if($feedname == '') $feedname = htmlspecialchars($url);
		mysql_query("INSERT IGNORE INTO `feeds` (`name`, `url`, `lastupdate`) VALUES ('". mysql_real_escape_string($feedname). "', '". mysql_real_escape_string($url)."', ".time().")");
		echo mysql_error();
		mysql_query("INSERT IGNORE INTO `feeds_subscription` (`feedid`, `userid`) VALUES ((SELECT `id` FROM `feeds` WHERE `url` = '". mysql_real_escape_string($url)."'), '". $user_id. "')");
		echo mysql_error();
	}
	
	if(!isset($_SESSION['add_csrf_hashes'])) $_SESSION['add_csrf_hashes'] = array();
	$s = sha1(mt_rand());
	$_SESSION['add_csrf_hashes'][] = $s;
	
	if(isset($_GET['mailchange']) and $_GET['mailchange'] == 'success')
			echo '<p class="okay">'._('Deine E-Mail-Adresse wurde erfolgreich geändert.').'</p>';
	
	if (isset($_POST['feedurl']) && !empty($_POST['feedurl'])) {
		if(!in_array($_REQUEST['hash'], $_SESSION['add_csrf_hashes'])) die("Error.");
		if(mysql_num_rows(mysql_query('SELECT * FROM feeds_subscription a WHERE a.userid = '.$user_id.' and 1 = (SELECT COUNT(*) FROM feeds f WHERE f.id = a.feedid AND f.url = "'.mysql_real_escape_string($_POST['feedurl']).'")')) == 0){
			if(strpos($_POST['feedurl'], 'http') !== 0){
				echo '<p class="error">'._('Es werden nur http:// und https://-URLs akzeptiert.').'</p>';
			}else{
				$add = add_feed($_POST['feedurl']);
				if($add === -7)
					echo '<p class="error">'._('Der Server, auf dem sich der Feed befindet, konnte nicht erreicht werden. Versuche es später erneut.').'</p>';
				else
					echo '<p class="okay">'._('Dein Feed wurde erfolgreich hinzugefügt. Nach dem nächsten Feed-Update (in spätestens 5 Minuten) wird dann links in der Leiste auch sein korrekter Titel angezeigt.').'</p>';
			}
		}else
			echo '<p class="error">'._('Du hast diesen Feed bereits abonniert.').'</p>';
	}
	if (!empty($_GET["del"])) {
		if(!in_array($_GET['hash'], $_SESSION['add_csrf_hashes'])) die("Error.");
		mysql_query("DELETE FROM `feeds_subscription` WHERE `feedid` ='". intval($_GET["del"]). "' AND `userid` =". $user_id);
		$abo_del_qry = mysql_query("SELECT 0 FROM `feeds_subscription` WHERE `feedid` = ". intval(($_GET["del"]))); 
		if (mysql_num_rows($abo_del_qry) == 0) {
			mysql_query("DELETE FROM `feeds_entries` WHERE feed_id = '". intval($_GET["del"]). "'"); 
			mysql_query("DELETE FROM `feeds` WHERE id = '". intval($_GET["del"]). "'");   
		}
		echo '<p class="okay">'._('Dein Abonnement des Feeds wurde erfolgreich entfernt.').'</p>';
	}
	
	$meq = mysql_query('SELECT * FROM user WHERE id = '.intval($user_id));
	$me = mysql_fetch_object($meq);
	
	if(isset($_POST['oldpw'])){
		$password = sha1($_POST["oldpw"]. $salt);
		$login_qry = mysql_query("SELECT `id` FROM `user` WHERE `id` = ". $user_id. " AND `password` = '". $password. "'");
		if (mysql_num_rows($login_qry) == 1) {
			if(isset($_POST['newpw']) and !empty($_POST['newpw'])){
				if($_POST['newpw'] != $_POST['newpw2'])
					echo '<p class="error">
						'._('Das neue Passwort muss zweimal gleich eingegeben werden.').'
					</p>';
				else {
					mysql_query("UPDATE user SET password = '".sha1($_POST["newpw"]. $salt)."' WHERE `id` = ". $user_id);
					echo '<p class="okay">
						'._('Das Passwort wurde geändert.').'
					</p>';
				}
			}
			if($_POST['mail'] != $me->mail){
				if(preg_match('/^[a-z0-9.\-+_]+@[a-z0-9.\-+_]+\.[a-z]+$/i', $_POST['mail']) == 0)
					echo '<p class="error">
						'.sprintf(_('„%s“ ist keine gültige E-Mail-Adresse. Wenn du das anders siehst, kontaktiere uns bitte.'), htmlspecialchars($_POST['mail'])).'
					</p>';
				else{
					mail($_POST['mail'], '['.$title.'] '._('Bestätigung der Änderung der E-Mail-Adresse'), sprintf(_('Hallo!
jemand möchte seine im %s angegebene E-Mailadresse zu dieser ändern.
Wenn du das warst, klicke bitte untenstehenden Link an. Wenn nicht,
ignoriere diese E-Mail einfach.

%s'), $title, $mainurl."setmail.php?user=".$me->id."&mail=".urlencode($_POST['mail'])."&hash=".sha1($me->id.$secret."CHANGEMAIL".$_POST['mail'])), 'From: '.$mailsender);
					echo '<p class="okay">';
					printf(_('Es wurde eine Bestätigung zur Änderung der Mailadresse an %s versandt.'), htmlspecialchars($_POST['mail']));
					echo '</p>';
				}
			}
		} else {
			echo '<p class="error">
				'._('Das aktuelle Passwort wurde nicht korrekt eingegeben!').'
			</p>';
		}
	}
	if(isset($_POST['locale'])){
		if(in_array($_POST['locale'], $locales)){
			mysql_query("UPDATE user SET locale = '".mysql_real_escape_string($_POST['locale'])."' WHERE `id` = ". $user_id);
			$locale = $_POST['locale'];
			putenv('LC_ALL='.$locale);
			setlocale(LC_ALL, $locale);
			echo '<p class="okay">';
			printf(_('Deine Sprache wurde auf %s umgestellt.'), $locale);
			echo '</p>';
			$me->locale = $locale;
		}
	}
	
	echo '<h3>'._('Lokalisierung').'</h3>';
	echo '<form action="settings.php" method="post">
			<select name="locale">';
	foreach ($locales as $loc) {
		echo '<option'.($loc == $me->locale ? ' selected="selected"' : '').'>'.$loc.' ('.$localenames[$loc].')</option>';
	}
	echo '</select>
			<input type="submit" value="'._('Speichern').'" />
	</form>';
	
	echo '<h3>'._('Persönliche Einstellungen').'</h3>';
	
	echo '<form action="settings.php" method="post">
		<table>
			<tr>
				<td class="top">'._('Aktuelles Passwort:').'</td>
				<td><input type="password" name="oldpw" value="" id="" /></td>
			</tr>
			<tr>
				<td class="top">'._('Neues Passwort:').'</td>
				<td><input type="password" name="newpw" value="" id="" /></td>
			</tr>
			<tr>
				<td class="top">'._('Neues Passwort wiederholen:').'</td>
				<td><input type="password" name="newpw2" value="" id="" /></td>
			</tr>
			<tr>
				<td class="top">'._('E-Mail-Adresse:').'</td>
				<td><input type="text" name="mail" placeholder="'._('name@domain.tld').'" value="'.$me->mail.'" id="" />
				<p class="info"><small>'._('Nicht wundern: Nachdem du deine E-Mail-Adresse geändert hast, wird hier weiterhin deine alte Adresse stehen, <br />bis du einen Link in einer E-Mail, die wir an deine neue Adresse senden, angeklickt hast!').'</small></p></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" value="'._('Speichern').'" /></td>
			</tr>
		</table>
	</form>';
	?>
	<h3><?php echo _('Feed abonnieren'); ?></h3>
	<form id="addsub" action="settings.php" method="POST">
	  <input type="text" name="feedurl" placeholder="http://example.com/feed.rss" value="" />
	  <input type="hidden" name="hash" value="<?php echo $s; ?>" />
	  <input type="submit" value="<?php echo _('Abonnieren'); ?>" /> 
	</form>
	<?php
	echo '<h3>'._('Abos').'</h3>';
	$feeds_qry = mysql_query("SELECT `feedid`, `feedname`, `alias`, `origname`, `feedurl`, `lastupdate`, `updates` FROM `view_feed_subscriptions` WHERE `userid` =". $user_id. " AND feedid > 0 ORDER by `feedname` asc");
	if(mysql_num_rows($feeds_qry) == 0){
		echo "<p>"._("Keine Feeds gefunden.")."</p>";
	} else {
		echo '<table class="abos">
				<tr>
				  <th>'._('Name').'</th>
				  <th>'._('Alias').'</th>
				  <th class="update_column">'._('Bei Update eines Artikels wieder als ungelesen anzeigen').'</th>
				  <th></th>
				</tr>';
		while ($row = mysql_fetch_assoc($feeds_qry)) {
			echo '<tr><td id="title_'.$row["feedid"].'">';
			if(time()-$row["lastupdate"] > 1000){
				echo '<img src="images/error.png" class="erroricon" alt="'._('Fehler').'" title="'._('Dieser Feed konnte kürzlich nicht erfolgreich abgerufen werden.').'" /> ';
			}
			echo '<a href="'.$row['feedurl'].'" class="feedlink" target="_blank">'.utf_correct($row["origname"]). '</a></td><td id="alias_'.$row["feedid"].'">'. utf_correct($row["alias"]). '</td>';
			echo '<td class="update_column"><input type="checkbox" class="updates_box"'.($row['updates'] ? ' checked="checked"' : '').' value="'.$row["feedid"].'" /></td>';
			echo '<td><a href="settings.php?del='. $row["feedid"]. '&hash='.$s.'">'._('Löschen').'</a> | <a href="javascript:editalias('.$row["feedid"].')" id="editaliaslink_'.$row["feedid"].'">'._('Alias setzen').'</a></td></tr>';
		}
		echo '</table>';
	}   
	
	?>
		  
	<div id="desknot" style="display:none;">
		<h3><?php echo _('Desktop Notifications'); ?></h3>
		<p class="info">
			<?php echo _('Nicht wundern: Einstellung wird nur für diesen Computer gespeichert (als Cookie).'); ?>
		</p>
		<p><input type="button" id="actnot" class="biggerbtn" value="<?php echo _('Desktop Notifications aktivieren'); ?>" />
		<input type="button" id="deanot" class="biggerbtn" value="<?php echo _('Desktop Notifications deaktivieren'); ?>" /></p>
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
				n = window.webkitNotifications.createNotification('images/gfr.png', '<?php echo _('Test'); ?>', '<?php echo _('erfolgreich'); ?>');
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
			if(alias == ''){
				alias = $("#title_"+id+" a").html();
				orig = '';
			}else{
				orig = alias;
			}
			$("#alias_"+id).html('<form action="" id="editalias_'+id+'"><input type="text" class="alias" value="'+alias+'" /> <input type="image" src="images/accept.png" alt="<?php echo _('Speichern'); ?>" value="<?php echo _('Speichern'); ?>" class="imagebtn" /> <a href="javascript:void(0);" class="imagebtn" onclick="$(\'#alias_'+id+'\').html(\''+orig+'\');$(\'#editaliaslink_'+id+'\').fadeIn();" title="<?php echo _('Abbrechen'); ?>"><img src="images/cancel.png" alt="<?php echo _('Abbrechen'); ?>" /></a></form>');
			$("#editaliaslink_"+id).hide();
			$("#editalias_"+id).bind('submit', function(){
				newa = $("#alias_"+id+" input.alias").val();
				$.get('settings_setalias_ajax.php?hash=<?php echo sha1($user_id.$salt.date('Ymd')); ?>&id='+id+'&alias='+escape(newa), function(){
						$("#alias_"+id).html(newa);
						$("#editaliaslink_"+id).fadeIn();
					});
				return false;
			});
		}
		$(".updates_box").bind("click", function(){
			var id = $(this).val();
			$.get('settings_setupdates_ajax.php?hash=<?php echo sha1($user_id.$salt.date('Ymd')); ?>&id='+id);
			var p = $(this).parent();
			x = p.css("background-color");
			p.stop().animate({backgroundColor: '#449944'}, function(){
				$(this).animate({backgroundColor:x});
			});
		});
	</script>
	
	</div></div>
	  <div id="right-gap"></div>
	  <div class="clear"></div>
	<?php
	require 'includes/application_footer.php';
} else {
	header('Location: index.php'); exit;       
}
?>                                           
