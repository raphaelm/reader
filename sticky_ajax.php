<?php
session_start();
if (isset($_SESSION['loggedin_as'])) {
	require_once 'includes/dbconnect.php';
	
	if(isset($_GET['sticky'])){
		mysql_query('REPLACE INTO sticky (user_id, article_id) VALUES ('.$_SESSION['loggedin_as'].', '.intval($_GET['sticky']).')');
	}elseif(isset($_GET['unsticky'])){
		if($_GET['unsticky'] == 'all'){
			mysql_query('DELETE FROM sticky WHERE user_id = '.$_SESSION['loggedin_as']);
			if(isset($_GET['mobile']))
				header('Location: m_sticky.php');
			else
				header('Location: sticky.php');
			exit;
		}
		mysql_query('DELETE FROM sticky WHERE user_id = '.$_SESSION['loggedin_as'].' and article_id = '.intval($_GET['unsticky']));
	}
	
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
	$json = array();
	$json['unread'] = array();
	while ($row = mysql_fetch_assoc($all_qry)) {
		$json['unread'][$row['feed_id']] = intval($row['c']);
	}        
	$json['unread']['all'] = array_sum($json['unread']);
	
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
	$json['unread']['sticky'] = $sticky;
	echo json_encode($json); 
	exit; 
	
} else {
	header('Location: index.php');
	exit;       
}
?>                                           
