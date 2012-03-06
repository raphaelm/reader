<?php
require_once 'includes/dbconnect.php';
if ($user_id) {
	require 'includes/application_header.php';
	require 'includes/application_navi.php';
	echo '<div id="right-col">';
	echo '<div id="wrap" class="reader-field"><h2>'._('Alle Feeds').'</h2><p>';
	if(!isset($_GET['show']) || $_GET['show'] == 'unread') echo '<strong>'._('Ungelesene Einträge').'</strong> &middot; '; else echo '<a href="?show=unread">'._('Ungelesene Einträge').'</a> &middot; ';
	if(isset($_GET['show']) && $_GET['show'] == 'all') echo '<strong>'._('Alle Einträge').'</strong> &middot; '; else echo '<a href="?show=all">'._('Alle Einträge').'</a> &middot; ';
	echo '<a href="markasread.php?feedid=all">'._('Alles als gelesen markieren').'</a>';
	echo '</p>';

	$dq = mysql_query("SELECT COUNT(`feedid`) as c, (SELECT COUNT(id) FROM feeds WHERE lastupdate < ".(time()-1000).") as c2 FROM `view_feed_subscriptions` WHERE `userid` =". $user_id. " AND lastupdate < ".(time()-1000));
	$d = mysql_fetch_object($dq);
	if($d->c > 7 or $d->c2 > 13){
		echo '<p class="error">
					'._('Wir leiden derzeit leider unter einem technischen Problem und hoffen, dass dieses bald behoben werden kann.').'
				</p>';
	}

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
							`user_id` = ". $user_id. "
						AND
							`feeds_read`.`article_id` = `feeds_entries`.`article_id`
					)
					AND"
				: '')."
				1 = (SELECT
						COUNT(`feedid`)
					FROM
						`feeds_subscription`
					WHERE
						`userid` =". $user_id. "
					AND
						`feeds_subscription`.`feedid` = `feeds_entries`.`feed_id`
				)
			ORDER by 
				timestamp DESC	
			LIMIT 30");

	while($r = mysql_fetch_object($lasttimestamp)) {$last = $r;}
	$lasttimestamp = $last->timestamp;

	$all_qry = mysql_query("SELECT
				`feed_id`,
				IF(
					((SELECT alias FROM feeds_subscription WHERE `userid` = ".$user_id." AND `feeds_subscription`.`feedid` = `feeds_entries`.`feed_id`) != ''),
					(SELECT alias FROM feeds_subscription WHERE `userid` = ".$user_id." AND `feeds_subscription`.`feedid` = `feeds_entries`.`feed_id`),
					`name`
				) as `feedtitle`,
				`feeds`.`url` as `feedurl`,
				`article_id`,
				`title`,
				`guid`,
				`timestamp`,
				(SELECT COUNT(*) FROM sticky s WHERE user_id = ".$user_id." AND s.article_id = `feeds_entries`.article_id) as `sticky`,
				`article_id`,
				`summary`,
				`updated`,
				`feeds_entries`.`url` as `articleurl`,
				(
					SELECT
						COUNT(`article_id`)
					FROM
						`feeds_read`
					WHERE
						`user_id` = ". $user_id. "
					AND
						`feeds_read`.`article_id` = `feeds_entries`.`article_id`
				) as `read_status`
				FROM
					`feeds_entries`
				INNER JOIN
					`feeds`
					ON
						`feeds`.`id` = `feeds_entries`.`feed_id`
				WHERE
					".(
					(!isset($_GET['show']) || $_GET['show'] == 'unread') ?
						"0 = (SELECT
								COUNT(`article_id`)
							FROM
								`feeds_read`
							WHERE
								`user_id` = ". $user_id. "
							AND
								`feeds_read`.`article_id` = `feeds_entries`.`article_id`
						)
						AND"
					: '')."
					1 = (SELECT
							COUNT(`feedid`)
						FROM
							`feeds_subscription`
						WHERE
							`userid` =". $user_id. "
						AND
							`feeds_subscription`.`feedid` = `feeds_entries`.`feed_id`
					)
					AND timestamp >= ".intval($lasttimestamp)."
				ORDER by 
					`timestamp` desc");
	if($lasttimestamp < 1) $lasttimestamp = 0;
	echo '<script type="text/javascript">
		var lasttimestamp = '.$lasttimestamp.';
	</script>';
	
	if(mysql_num_rows($all_qry) == 0){
		echo '<p class="info">
					'.sprintf(_('Dieser Feed besitzt keine%s Einträge. Wenn du ihn gerade erst aboniert hast, kann es bis zu fünf Minuten dauern, bis hier Einträge erscheinen. Außerdem werden keine Einträge angezeigt, die älter als 30 Tage sind.'), ((!isset($_GET['show']) || $_GET['show'] == 'unread') ? _(' ungelesenen') : '')).'
			</p>
			<script type="text/javascript">
				window.setTimeout(function(){
						$("#wrap").append("<p class=\"reload\"><a href=\'javascript:location.reload();\'>'._('Neu laden').'</a></p>");
					}, 120000);
			</script>';
	}
	while ($row = mysql_fetch_assoc($all_qry)) {
		echo '<div id="article_'.$row["article_id"].'"'.($row["read_status"] == 0 ? ' class="unreadarticle"' : ' class="readarticle'.(($row["sticky"] == 1) ? ' sticky' : '').'"').'>';
		echo '<a href="'. $row["articleurl"]. '" class="titlelink" target="_blank">'. utf_correct($row["title"]). '</a>';
		if($row["updated"] > 0){
			echo '<span class="updated" title="'.sprintf(_("Dieser Artikel hat sich %d mal geändert"), $row["updated"]).'">'.$row["updated"].'</span>';
		}
		echo '<br />';
		echo '<em>'. date(_("d.m.Y - H:i"), $row["timestamp"]). ': '. $row["feedtitle"]. '</em>';
		if($row["sticky"] == 1) echo ' &middot; <a href="javascript:unsticky('.$row["article_id"].');" class="stickylink">'._('nicht merken').'</a>';
		else echo ' &middot; <a href="javascript:sticky('.$row["article_id"].');" class="stickylink">'._('merken').'</a>';
		echo '<br /><div class="sum">'. utf_correct(gzuncompress($row["summary"])). '</div><div class="clear"></div></div>';
	}      
	
	echo '</div></div>
		<div id="right-gap"></div>
		<div class="clear"></div>'; 
		
	require 'includes/application_footer.php';
	
} else {
	header('Location: index.php');
	exit;       
}
