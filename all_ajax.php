<?php
session_start();
if (isset($_SESSION['loggedin_as'])) {
	require_once 'includes/dbconnect.php';
	require_once 'includes/functions.php';

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
				1 = (SELECT
						COUNT(`feedid`)
					FROM
						`feeds_subscription`
					WHERE
						`userid` =". $_SESSION['loggedin_as']. "
						AND
						`feeds_subscription`.`feedid` = `feeds_entries`.`feed_id`
					)
				
				AND timestamp < ".intval($_GET['lasttimestamp'])."		
			ORDER by 
				timestamp DESC	
			LIMIT 30");
			
	while($r = mysql_fetch_object($lasttimestamp)) {$last = $r;}
	$lasttimestamp = $last->timestamp;
	
	$all_qry = mysql_query("SELECT
				`feed_id`,
				IF(
					((SELECT alias FROM feeds_subscription WHERE `userid` = ".$_SESSION['loggedin_as']." AND `feeds_subscription`.`feedid` = `feeds_entries`.`feed_id`) != ''),
					(SELECT alias FROM feeds_subscription WHERE `userid` = ".$_SESSION['loggedin_as']." AND `feeds_subscription`.`feedid` = `feeds_entries`.`feed_id`),
					`name`
				) as `feedtitle`,
				`feeds`.`url` as `feedurl`,
				`article_id`,
				`title`,
				`guid`,
				`timestamp`,
				(SELECT COUNT(*) FROM sticky s WHERE user_id = ".$_SESSION['loggedin_as']." AND s.article_id = `feeds_entries`.article_id) as `sticky`,
				`summary`,
				`article_id`,
				`feeds_entries`.`url` as `articleurl`,
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
			INNER JOIN
				`feeds`
					ON `feeds`.`id` = `feeds_entries`.`feed_id`
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
				1 = (SELECT
						COUNT(`feedid`)
					FROM
						`feeds_subscription`
					WHERE
						`userid` =". $_SESSION['loggedin_as']. "
						AND
						`feeds_subscription`.`feedid` = `feeds_entries`.`feed_id`
					)
				AND timestamp < ".intval($_GET['lasttimestamp'])."
				AND timestamp >= ".intval($lasttimestamp)."
			ORDER by 
				`timestamp` desc");
			
	if($lasttimestamp < 1) $lasttimestamp = 0;
	echo '<script type="text/javascript">
			lasttimestamp = '.$lasttimestamp.';
		</script>';
			 
	if(mysql_num_rows($all_qry) == 0) die('<!-- NOTHING MORE -->');
	
	while ($row = mysql_fetch_assoc($all_qry)) {
		echo '<div id="article_'.$row["article_id"].'"'.($row["read_status"] == 0 ? ' class="unreadarticle"' : ' class="readarticle'.(($row["sticky"] == 1) ? ' sticky' : '').'"').'>';
		echo '<a '.   (isset($_GET['mobile']) ? 'onclick="togglearticle('.$row["article_id"].')" href="javascript:void(0);' : 'target="_blank" href="'. $row["articleurl"]). '" class="titlelink">';
		echo utf_correct($row["title"]). '</a>';
		echo (isset($_GET['mobile']) ? '' : '<br />').'<em>'. date(_("d.m.Y - H:i"), $row["timestamp"]). ': '. utf_correct($row["feedtitle"]). '</em>';
		if($row["sticky"] == 1) echo ' &middot; <a href="javascript:unsticky('.$row["article_id"].');" class="stickylink">'._('nicht merken').'</a>';
		else echo ' &middot; <a href="javascript:sticky('.$row["article_id"].');" class="stickylink">'._('merken').'</a>';
		echo '<br />';
		echo '<div class="sum"'.(isset($_GET['mobile']) ? ' style="display:none"><a href="'.$row["articleurl"].'" target="_blank">'._('zum Originalbeitrag').'</a><br />' : '>' ). utf_correct(gzuncompress($row["summary"])). '</div></div>';
	}        

} else {
	echo json_encode(array('error' => _('not logged in'))); 
	exit;       
}
