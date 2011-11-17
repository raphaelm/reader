<?php require_once("config.inc.php") ?>
<!DOCTYPE html>
<html>
  <head>
      <title><?php echo $title ?></title>
      <meta http-equiv="content-type" content="text/html;charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="stylem.css" />
      <meta http-equiv="Content-Style-Type" content="text/css" />
	  <script type="text/javascript" src="jquery.js"></script>
	  <script type="text/javascript" src="readerm.js"></script>
	  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
  </head>
  <body>
<?php
function selarea($actual){
	if(strlen($actual) > 14)
		$actual = substr($actual, 0, 11)."…";
	echo "<div id=\"selarea\"><strong onclick='$(\"#selarea ul\").toggle();'>$actual</strong><ul>";
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
			`feed_id`
		ORDER by 
			`timestamp` desc");
			
	$unread = array();
	while ($row = mysql_fetch_assoc($all_qry)) {
		$unread[$row['feed_id']] = intval($row['c']);
	}        
	$unread["all"] = array_sum($unread);
	
	echo "<li><a href=\"m_all.php\">Alle Feeds <span id='unreadcount_all'>".($unread["all"] > 0 ? '('.$unread["all"].')': '')."</span></a></li>";
		  
	$feeds_qry = mysql_query("SELECT `feedid`, `feedname` FROM `view_feed_subscriptions` WHERE `userid` =". $_SESSION['loggedin_as']. " AND feedid > 0 ORDER by `feedname` asc");
	if(mysql_num_rows($feeds_qry) == 0){
		echo "<p>Keine Feeds gefunden.</p>";
	} else {
		while ($row = mysql_fetch_assoc($feeds_qry)) {
			echo '<li id="feednavi_'.$row["feedid"].'"><a href="m_feeds.php?feedid='. $row["feedid"]. '">'. utf_correct($row["feedname"]);
			echo ' <span id="unreadcount_'.$row["feedid"].'">'.($unread[$row["feedid"]] > 0 ? '('.$unread[$row["feedid"]].')': '').'</span></a></li>';
		}
	}
	echo '</ul></div> <a href="logout.php?mobile=true" id="logout"><img src="images/logout.gif" alt="Ausloggen" /></a>';
}

?>
