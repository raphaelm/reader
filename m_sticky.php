<?php
define('IS_MOBILE', true);
session_start();
if (isset($_SESSION['loggedin_as'])) {
	require_once 'includes/dbconnect.php';
	require_once 'includes/functions.php';
	require 'includes/mobile_header.php';
	?>
	<div id="wrap">
	<div id="topbar">
		<a href="m_all.php"><img src="images/gfr.gif" alt="geek's factory reader" /></a> <?php selarea(_("Gemerkte Artikel")); ?>
	</div>
	<p id="subnav">
		<a href="sticky_ajax.php?unsticky=all&mobile=true"><?php echo _("Alle entfernen"); ?></a>
	</p>
	<?php
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
					ON
						`feeds`.`id` = `feeds_entries`.`feed_id`
				WHERE
					1 = (SELECT
							COUNT(`article_id`)
						FROM
							`sticky`
						WHERE
							`user_id` = ". $_SESSION['loggedin_as']. "
						AND
							`sticky`.`article_id` = `feeds_entries`.`article_id`
					)
					AND
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
					`timestamp` desc");
					
	if(mysql_num_rows($all_qry) == 0){
		echo '<p class="info">
				'._('Du hast dir keine Einträge gemerkt!').'
			</p>';
	}
	while ($row = mysql_fetch_assoc($all_qry)) {
		echo '<div id="article_'.$row["article_id"].'" class="sticky">';
		echo '<a href="javascript:void(0);" class="titlelink" onclick="togglearticle('.$row["article_id"].')">'. utf_correct($row["title"]). '</a>';
		echo '<em>'. date(_("d.m.Y - H:i"), $row["timestamp"]). ': '. utf_correct($row["feedtitle"]). '</em>';
		echo ' &middot; <a href="javascript:unstickyremove('.$row["article_id"].');" class="stickylink">'._('aus Merkliste entfernen').'</a>';
		echo '<br />';
		echo '<div class="sum"><a href="'.$row["articleurl"].'" target="_blank">'._('zum Originalbeitrag').'</a><br />'. utf_correct(gzuncompress($row["summary"])). '</div></div>';
	}     
	
	echo '</div>';
	require 'includes/mobile_footer.php';
} else {
	header('Location: index.php');
	exit;       
}
