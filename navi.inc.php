<?php
include_once('functions.inc.php');
if (isset($_SESSION['loggedin_as'])) {
	/* New? */
		$all_qry = mysql_query("SELECT
					COUNT(`feed_id`) as c,
					`feed_id`
				FROM
					`feeds_entries`
				INNER JOIN
					`feeds`
						ON `feeds`.`id` = `feeds_entries`.`feed_id`
				WHERE
					0 = (SELECT
							COUNT(`article_id`)
						FROM
							`feeds_read`
						WHERE
							`user_id` = ". $_SESSION['loggedin_as']. "
							AND
							`feeds_read`.`article_id` = `feeds_entries`.`article_id`
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
				GROUP by 
					`feed_id`");
	$unread = array();
	while ($row = mysql_fetch_assoc($all_qry)) {
		$unread[$row['feed_id']] = intval($row['c']);
	}        
	$unread["all"] = array_sum($unread);
	$sticky_qry = mysql_query("SELECT
				COUNT(`feed_id`) as c
			FROM
				`feeds_entries`
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
					)");
	$sticky = mysql_fetch_object($sticky_qry);
	$sticky = $sticky->c;
	/* End: New? */
	
	echo '<div id="left-col">
		  <a href="index.php"><img src="images/logo.png" id="logo" /></a>
		  <div id="navi">
		  <ul>
		  <li id="feednavi_all"><a href="all.php"><img src="images/newspaper.png" class="favicon" alt="" /> <span class="text">Alle Feeds <span class="unread" id="unreadcount_all">'.($unread["all"] > 0 ? '('.$unread["all"].')': '').'</span></span></a></li>
		  <li><a href="sticky.php"><img src="images/star.png" class="favicon" alt="" /> <span class="text">Merkliste <span class="unread" id="unreadcount_sticky">'.($sticky > 0 ? '('.$sticky.')': '').'</span></span></a></li>
		  <li><a href="settings.php"><img src="images/wrench.png" class="favicon" alt="" /> <span class="text">Einstellungen</span></a></li>
		  <li><a href="logout.php"><img src="images/door_out.png" class="favicon" alt="" /> <span class="text">Ausloggen</span></a></li>
		  <li class="feednavi_hr"></li>
		  ';
		  
	$feeds_qry = mysql_query("SELECT `feedid`, `feedname`, `lastupdate` FROM `view_feed_subscriptions` WHERE `userid` =". $_SESSION['loggedin_as']. " AND feedid > 0 ORDER by `feedname` asc");
	if(mysql_num_rows($feeds_qry) == 0){
		echo "<p>Keine Feeds gefunden.</p>";
	} else {
		while ($row = mysql_fetch_assoc($feeds_qry)) {
			echo '<li id="feednavi_'.$row["feedid"].'"><a href="feeds.php?feedid='. $row["feedid"]. '">';
			echo '<img class="favicon" src="favicons/'. $row["feedid"]. '.png" alt="" /> <span class="text">'. utf_correct($row["feedname"]).' <span id="unreadcount_'.$row["feedid"].'" class="unread">'.($unread[$row["feedid"]] > 0 ? '('.$unread[$row["feedid"]].')': '').'</span>';
			if(time()-$row["lastupdate"] > 1000){
				echo ' <img src="images/error.png" class="erroricon" alt="Fehler" title="Dieser Feed konnte kürzlich nicht erfolgreich abgerufen werden." />';
			}
			echo '</span></a></li>';
		}
	}
	echo '<li class="feednavi_hr"></li>
		  <li id="infoline">Entwickler und Betreiber: <a href="http://geeksfactory.de" target="_blank">geek\'s factory</a><br /><a href="http://git.geeksfactory.de/reader.git" target="_blank">get the source!</a><br />Icons von <a href="http://famfamfam.com" target="_blank">famfamfam.com</a></li></ul>
		  </div>
		  </div>';
}
?>
