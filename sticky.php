<?php
require_once 'includes/dbconnect.php';
if ($user_id) {
	require 'includes/application_header.php';
	require 'includes/application_navi.php';
	echo '<div id="right-col">';
	echo '<div id="wrap" class="reader-field removeunstickied"><h2>'._('Gemerkte Artikel').'</h2><p>';
	echo '<a href="sticky_ajax.php?unsticky=all">'._('Alle entfernen').'</a>';
	echo '</p>';
	
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
					1 = (SELECT
							COUNT(`article_id`)
						FROM
							`sticky`
						WHERE
							`user_id` = ". $user_id. "
						AND
							`sticky`.`article_id` = `feeds_entries`.`article_id`
					)
					AND
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
					`timestamp` desc");
					
	if(mysql_num_rows($all_qry) == 0){
		echo '<p class="info">
				'._('Du hast dir keine Einträge gemerkt!').'
			</p>';
	}
	while ($row = mysql_fetch_assoc($all_qry)) {
		echo '<div id="article_'.$row["article_id"].'" class="sticky">';
		echo '<a href="'. $row["articleurl"]. '" class="titlelink" target="_blank">'. utf_correct($row["title"]). '</a>';
		if($row["updated"] > 0){
			echo '<span class="updated" title="'.sprintf(_("Dieser Artikel hat sich %d mal geändert"), $row["updated"]).'">'.$row["updated"].'</span>';
		}
		echo '<br />';
		echo '<em>'. date(_("d.m.Y - H:i"), $row["timestamp"]). ': '. $row["feedtitle"]. '</em>';
		echo ' &middot; <a href="javascript:unstickyremove('.$row["article_id"].');">'._('aus Merkliste entfernen').'</a>';
		echo '<br />';
		echo '<div class="sum">'. utf_correct(gzuncompress($row["summary"])). '</div><div class="clear"></div></div>';
	}      
	
	echo '</div></div>
		<div id="right-gap"></div>
		<div class="clear"></div>
		<script type="text/javascript">
			register_scroll_hotkeys();
			register_focus_handler();
		</script>'; 
		
	require 'includes/application_footer.php';
	
} else {
	header('Location: index.php');
	exit;       
}
