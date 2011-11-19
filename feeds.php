<?php
session_start();
if (isset($_SESSION['loggedin_as'])) {
	if (empty($_GET["feedid"])) {
		header('Location: all.php'); exit;
	}
	require_once("dbconnect.php");
	include('headeri.php');
	include('navi.inc.php');
	
	echo '<div id="right-col"><div id="wrap">';
	if (!empty($_GET["feedid"])) {
		$is_sub = mysql_query("SELECT `feeds`.`name`, alias, feeds.lastupdate FROM `feeds_subscription` INNER JOIN `feeds` ON `feeds`.`id` = `feeds_subscription`.`feedid` WHERE `feedid` = ". intval(($_GET["feedid"])). " AND `userid` =". $_SESSION['loggedin_as']); 
		if (mysql_num_rows($is_sub) == 1) {
			$feed = mysql_fetch_assoc($is_sub);
			echo '<h2>'.utf_correct(($feed['alias']) ? $feed['alias'] : $feed['name']).'</h2><p>';
			if(!isset($_GET['show']) || $_GET['show'] == 'unread') echo '<strong>'._('Ungelesene Einträge').'</strong> &middot; '; else echo '<a href="?feedid='.intval($_GET['feedid']).'&amp;show=unread">'._('Ungelesene Einträge').'</a> &middot; ';
			if(isset($_GET['show']) && $_GET['show'] == 'all') echo '<strong>'._('Alle Einträge').'</strong> &middot; '; else echo '<a href="?feedid='.intval($_GET['feedid']).'&amp;show=all">'._('Alle Einträge').'</a> &middot; ';
			echo '<a href="markasread.php?feedid='.intval($_GET['feedid']).'">'._('Alles in diesem Feed als gelesen markieren').'</a>
			</p>';
			if($feed['lastupdate'] < time()-1000)
				echo '<p class="error">
				'._('Das Abrufen dieses Feeds schlug leider kürzlich fehl. Stelle sicher, dass die Feed-Adresse noch aktuell ist. Wenn alles stimmt, sollte in wenigen Minuten auch alles wieder gehen.').'
				</p>';

			$lasttimestamp = mysql_query("SELECT 
						`timestamp`
					FROM
						`feeds_entries`
					WHERE
						".(
						(!isset($_GET['show']) || $_GET['show'] == 'unread') ?
							"0 = (SELECT
								COUNT(`article_id`)
							FROM
								`feeds_read`
							WHERE
								`user_id` = ". $_SESSION['loggedin_as']. "
								AND
								`feeds_read`.`article_id` = `feeds_entries`.`article_id`
							)
							AND"
						: '')."
						`feed_id` = ". intval(($_GET["feedid"])). " 
					ORDER by 
						timestamp DESC	
					LIMIT 30");

			while($r = mysql_fetch_object($lasttimestamp)) {$last = $r;}
			$lasttimestamp = $last->timestamp;

			$entries_qry = mysql_query("SELECT 
						`article_id`, 
						`title`, 
						`url`, 
						(SELECT COUNT(*) FROM sticky s WHERE user_id = ".$_SESSION['loggedin_as']." AND s.article_id = `feeds_entries`.article_id) as `sticky`,
						`timestamp`, 
						`summary`,
						(
							SELECT
								COUNT(`article_id`)
							FROM
								`feeds_read`
							WHERE
								`user_id` = ". $_SESSION['loggedin_as']. "
								AND
								`feeds_read`.`article_id` = `feeds_entries`.`article_id`
						) as `read_status`
					FROM
						`feeds_entries` 
					WHERE 
						`feed_id` = ". intval(($_GET["feedid"])). " 
						".(
						(!isset($_GET['show']) || $_GET['show'] == 'unread') ?
							"AND 0 = (SELECT
									COUNT(`article_id`)
								FROM
									`feeds_read`
								WHERE
									`user_id` = ". $_SESSION['loggedin_as']. "
									AND
									`feeds_read`.`article_id` = `feeds_entries`.`article_id`
							)"
						: '')." 
						AND timestamp >= ".intval($lasttimestamp)."
					ORDER by 
						`timestamp` desc"); 
			if($lasttimestamp < 1) $lasttimestamp = 0;
			echo '<script type="text/javascript">
					var lasttimestamp = '.$lasttimestamp.';
				</script>';
			if(mysql_num_rows($entries_qry) == 0){
				echo '<p class="info">
					'.sprintf(_('Dieser Feed besitzt keine%s Einträge. Wenn du ihn gerade erst aboniert hast, kann es bis zu fünf Minuten dauern, bis hier Einträge erscheinen. Außerdem werden keine Einträge angezeigt, die älter als 30 Tage sind.'), ((!isset($_GET['show']) || $_GET['show'] == 'unread') ? _(' ungelesenen') : '')).'
					</p>
					<script type="text/javascript">
						window.setTimeout(function(){
								$("#wrap").append("<p class=\"reload\"><a href=\'javascript:location.reload();\'>'._('Neu laden').'</a></p>");
							}, 120000);
					</script>';
			}
			while ($row = mysql_fetch_assoc($entries_qry)) {
				echo '<div id="article_'.$row["article_id"].'"'.($row["read_status"] == 0 ? ' class="unreadarticle"' : ' class="readarticle'.(($row["sticky"] == 1) ? ' sticky' : '').'"').'>';
				echo '<a href="'. $row["url"]. '" class="titlelink" target="_blank">'. utf_correct($row["title"]). '</a><br />';
				echo '<em>'. date(_("d.m.Y - H:i"), $row["timestamp"]). '</em>';
				if($row["sticky"] == 1) echo ' &middot; <a href="javascript:unsticky('.$row["article_id"].');" class="stickylink">'._('nicht merken').'</a>';
				else echo ' &middot; <a href="javascript:sticky('.$row["article_id"].');" class="stickylink">'._('merken').'</a>';
				echo '<br /><div class="sum">'. utf_correct(gzuncompress($row["summary"])). '</div><div class="clear"></div></div>';
			} 
		} else {
			echo '<p class="error">'._('Du abonnierst diesen Feed nicht.').'</p>';
		}   
	}
	echo '</div></div>
		<div id="right-gap"></div>
		<div class="clear"></div>'; 
	include('footl.php');
} else {
	header('Location: index.php'); 
	exit;       
}
?>

