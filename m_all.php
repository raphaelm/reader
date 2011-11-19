<?php
define('IS_MOBILE', true);
session_start();
if (isset($_SESSION['loggedin_as'])) {
	require_once("dbconnect.php");
	require_once("functions.inc.php");
	include('headerm.php');
	?>
	<div id="wrap">
	<div id="topbar">
		<a href="m_all.php"><img src="images/gfr.gif" alt="geek's factory reader" /></a> <?php selarea(_("Alle Feeds")); ?>
	</div>
	<p id="subnav">
		<?php if(!isset($_GET['show']) || $_GET['show'] == 'unread') echo '<strong>'.('Ungelesene Einträge').'</strong> &middot; '; else echo '<a href="?show=unread">'._('Ungelesene Einträge').'</a> &middot; ';
		if(isset($_GET['show']) && $_GET['show'] == 'all') echo '<strong>'.('Alle Einträge').'</strong>'; else echo '<a href="?show=all">'._('Alle Einträge').'</a>';
		?><br />
		<a href="markasread.php?feedid=all&mobile=true"><?php echo _("Alles als gelesen markieren"); ?></a>
	</p>
	<?php
	
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
				`article_id`,
				(SELECT COUNT(*) FROM sticky s WHERE user_id = ".$_SESSION['loggedin_as']." AND s.article_id = `feeds_entries`.article_id) as `sticky`,
				`summary`,
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
				</p>';
	}
	while ($row = mysql_fetch_assoc($all_qry)) {
		echo '<div id="article_'.$row["article_id"].'"'.($row["read_status"] == 0 ? ' class="unreadarticle"' : ' class="readarticle'.(($row["sticky"] == 1) ? ' sticky' : '').'"').'>';
		echo '<a href="javascript:void(0);" class="titlelink" onclick="togglearticle('.$row["article_id"].')">'. utf_correct($row["title"]). '</a>';
		echo '<em>'. date(_("d.m.Y H:i"), $row["timestamp"]). ': '. utf_correct($row["feedtitle"]). '</em>';
		if($row["sticky"] == 1) echo ' &middot; <a href="javascript:unsticky('.$row["article_id"].');" class="stickylink">'._('nicht merken').'</a>';
		else echo ' &middot; <a href="javascript:sticky('.$row["article_id"].');" class="stickylink">'._('merken').'</a>';
		echo '<br />';
		echo '<div class="sum"><a href="'.$row["articleurl"].'" target="_blank">'._('zum Originalbeitrag').'</a><br />'. utf_correct(gzuncompress($row["summary"])). '</div></div>';
	}      
	echo "<a href='javascript:loadmore();' class='loadmore'>"._('Mehr laden')."</a>";
	echo '</div>';
	include('footm.php');
}
else {
header('Location: index.php'); exit;       
}
?>
