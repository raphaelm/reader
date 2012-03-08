<?php
require_once 'includes/dbconnect.php';
session_start();
if ($user_id) {
	require_once 'includes/functions.php';
	require 'includes/application_header.php';
	require 'includes/application_navi.php';
	
	echo '<div id="right-col">
		  <div id="wrap" class="settingspage"><h2>'._('Abonnements importieren').'</h2>';
	
	if(!isset($_SESSION['add_csrf_hashes'])) $_SESSION['add_csrf_hashes'] = array();
	$s = sha1(mt_rand());
	$_SESSION['add_csrf_hashes'][] = $s;
	
	if(isset($_POST['feedurl'])){
		if(!in_array($_REQUEST['hash'], $_SESSION['add_csrf_hashes'])) die("Error.");
		foreach ($_POST['feedurl'] as $i => $url) {
			$feedname = $_POST['feedalias'][$i];
			if(mysql_num_rows(mysql_query('SELECT * FROM feeds_subscription a WHERE a.userid = '.$user_id.' and 1 = (SELECT COUNT(*) FROM feeds f WHERE f.id = a.feedid AND f.url = "'.mysql_real_escape_string($url).'")')) == 0){
				if(strpos($url, 'http') !== 0){
					echo '<p class="error">'._('Es werden nur http:// und https://-URLs akzeptiert.').'</p>';
				}else{
					mysql_query("INSERT IGNORE INTO `feeds` (`name`, `url`, `lastupdate`) VALUES ('". mysql_real_escape_string($feedname). "', '". mysql_real_escape_string($url)."', ".time().")");
					mysql_query("INSERT IGNORE INTO `feeds_subscription` (`feedid`, `userid`, `alias`) VALUES ((SELECT `id` FROM `feeds` WHERE `url` = '". mysql_real_escape_string($url)."'), '". $user_id. "', '".mysql_real_escape_string($feedname)."')");
				}
			}else
				echo '<p class="error">'._('Du hast diesen Feed bereits abonniert.').'</p>';
			
		}
		echo '<p class="okay">'._('Die Feeds wurden erfolgreich hinzugefügt. Es kann allerdings wenige Minuten dauern, bis ihre Beiträge erscheinen.').'</p>';
		
	}elseif(isset($_FILES['opmlfile']) /* || other-type */){
		$feeds = false;
		if(isset($_FILES['opmlfile'])){
			// double if is useless but I will thank myself for it as soon
			// as I add more import formats
			
			require 'includes/opml_parser.php';
						
			$o = new OPML_Parser();
			$p = $o->parse(file_get_contents($_FILES['opmlfile']['tmp_name']));
			if($p){
				$feeds = $o->get_feeds();
			} else {
				if($o->get_errors()){
					echo '<div class="error">'._('Diese Datei enthält XML-Fehler.');
					echo ' <a href="javascript:void(0);" onclick="$(\'#xmlerrordetails\').show();$(this).remove();">'._('Details anzeigen').'</a>';
					echo '<div id="xmlerrordetails" style="display: none;">';
					foreach ($o->get_errors() as $error) {
						echo 'Error '.$error->code.': ';
						echo trim($error->message);
						echo '<br />Line: '.$error->line;
						echo '<br />Column: '.$error->column;
						echo '<br />--------------------------------------------<br />';
					}
					echo '</div>';
					echo '</div>';
				} else {
					echo '<p class="error">'._('Diese Datei konnte leider nicht als OPML-Datei erkannt werden.').'</p>';
				}
			}
			if($feeds){
				echo '<h3>'._('Feeds auswählen').'</h3>';
				echo '<form action="import.php" method="post"> <input type="hidden" name="hash" value="'.$s.'" /><table>';
				$i = 0;
				foreach ($feeds as $feed) {
					$i++;
					if(strpos($feed['url'], 'http') !== 0){
						$possible = false;
						$error = _('Es werden nur http:// und https://-URLs akzeptiert.');
					}elseif(mysql_num_rows(mysql_query('SELECT * FROM feeds_subscription a WHERE a.userid = '.$user_id.' and 1 = (SELECT COUNT(*) FROM feeds f WHERE f.id = a.feedid AND f.url = "'.mysql_real_escape_string($feed['url']).'")')) > 0){
						$possible = false;
						$error = _('Du hast diesen Feed bereits abonniert.');
					}else{
						$possible = true;
					}
					
					$id = md5($feed['title']);
					echo '<tr>';
					echo '<td>';
					if($possible){
						echo '<input type="checkbox" checked="checked" name="feedurl['.$i.']" value="'.htmlspecialchars($feed['url']).'" id="box_'.$id.'" />';
					}
					echo '</td>';
					echo '<td><input type="text" name="feedalias['.$i.']" value="';
					echo htmlspecialchars($feed['title']);
					echo '" /></td>';
					echo '<td>';
					echo htmlspecialchars($feed['url']);
					echo '</td>';
					echo '<td class="err">';
					if(!$possible){
						echo $error;
					}
					echo '</td>';
					echo '</tr>';
				}
				echo '<tr>
					<td colspan="4">
						<input type="submit" value="'._('Feeds hinzufügen').'" />
					</td>
				</tr>';
				echo '</table></form>';
			}
			
		}
		
	}else{
		echo '<h3>'._('OPML-Import (Google Reader, Mozilla Thunderbird, …)').'</h3>';
		echo '<form action="import.php" method="post" enctype="multipart/form-data">
				<input type="file" name="opmlfile" value="" id="" />
				<input type="submit" value="'._('Import starten').'" />
		</form>';
	}
	
	?>
	
	</div></div>
	  <div id="right-gap"></div>
	  <div class="clear"></div>
	<?php
	require 'includes/application_footer.php';
} else {
	header('Location: index.php'); exit;       
}
?>                                           
