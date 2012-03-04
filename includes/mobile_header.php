<?php 
require_once 'includes/dbconnect.php';
require 'includes/setlocale.php'; ?>
<!DOCTYPE html>
<html>
  <head>
      <title><?php echo $title ?></title>
      <meta http-equiv="content-type" content="text/html;charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="style_mobile.css" />
      <meta http-equiv="Content-Style-Type" content="text/css" />
      <?php require_once 'includes/jslocale.php'; ?>
	  <script type="text/javascript" src="js/phpjs.js"></script>
	  <script type="text/javascript" src="js/jquery.js"></script>
	  <script type="text/javascript" src="js/reader_mobile.js"></script>
	  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
  </head>
  <body>
<?php
function selarea($actual){
	global $locale, $locales, $user_id;
	/*if(strlen($actual) > 14)
		$actual = substr($actual, 0, 11)."…";*/
	echo '<a href="logout.php?mobile=true" id="logout"><img src="images/door_out.png" alt="'._('Ausloggen').'" /></a>';
	/*echo '<div class="select"><strong><img src="i18n/flags/'.$locale.'.gif" alt="'.$locale.'" /></strong><ul>';
	foreach ($locales as $loc) {
		$_GET['locale'] = $loc;
		
		echo '<li><a href="?'.http_build_query($_GET).'"><img src="i18n/flags/'.$loc.'.gif" alt="'.$loc.'" /></a></li>';
	}
	echo '</ul></div>';*/
	echo "<div class=\"select feedselect\"><strong>$actual</strong><ul>";
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
					`user_id` = ". $user_id. "
					AND
					`feeds_read`.`article_id` = `feeds_entries`.`article_id`
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
		GROUP by 
			`feed_id`
		ORDER by 
			`timestamp` desc");
			
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
					)");
	$sticky = mysql_fetch_object($sticky_qry);
	$sticky = $sticky->c;
	
	echo "<li><a href=\"m_all.php\">"._("Alle Feeds")." <span id='unreadcount_all'>".($unread["all"] > 0 ? '('.$unread["all"].')': '')."</span></a></li>";
	echo "<li><a href=\"m_sticky.php\">"._("Merkliste")." <span id='unreadcount_sticky'>".($sticky > 0 ? '('.$sticky.')': '')."</span></a></li>";
		  
	$feeds_qry = mysql_query("SELECT `feedid`, `feedname` FROM `view_feed_subscriptions` WHERE `userid` =". $user_id. " AND feedid > 0 ORDER by `feedname` asc");
	if(mysql_num_rows($feeds_qry) == 0){
		echo "<p>Keine Feeds gefunden.</p>";
	} else {
		while ($row = mysql_fetch_assoc($feeds_qry)) {
			echo '<li id="feednavi_'.$row["feedid"].'"><a href="m_feeds.php?feedid='. $row["feedid"]. '">'. utf_correct($row["feedname"]);
			echo ' <span id="unreadcount_'.$row["feedid"].'">'.($unread[$row["feedid"]] > 0 ? '('.$unread[$row["feedid"]].')': '').'</span></a></li>';
		}
	}
	echo '</ul></div>';
}

?>
