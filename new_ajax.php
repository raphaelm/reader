<?php
require_once 'includes/dbconnect.php';
if ($user_id) {

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
	$json['unread']['sticky'] = $sticky;
	echo json_encode($json); 
	exit; 
} else {
	echo json_encode(array('error' => _('Nicht eingeloggt!'))); 
	exit;       
}
?>

