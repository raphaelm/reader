<?php
require_once 'includes/dbconnect.php';
if ($user_id) {
	
	if(isset($_GET['feedid'])){
		// alles als gelesen markieren
		if($_GET['feedid'] == 'all') 
			$query = "SELECT
						`feed_id`,
						`article_id`
					FROM
						`feeds_entries`
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
					ORDER by
						`timestamp` desc";
		else 
			$query = "SELECT
						`feed_id`,
						`article_id`
					FROM
						`feeds_entries`
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
						`feed_id` = ".intval($_GET['feedid'])."
					ORDER by 
						`timestamp` desc";
         
        $iq = "INSERT INTO `feeds_read` (`article_id`, `user_id`) VALUES ";
        $iqval = array();
        $dummy = mysql_query($query);
        while($row = mysql_fetch_assoc($dummy)){
			$iqval[] = "(".$row['article_id'].", ".$user_id.")";
		}
		$iq .= join(", ", $iqval);
		mysql_query($iq);
		if($_GET['feedid'] == 'all')
			header('Location: '.(isset($_REQUEST['mobile']) ? 'm_' : '').'all.php');
		else
			header('Location: '.(isset($_REQUEST['mobile']) ? 'm_' : '').'feeds.php?feedid='.intval($_GET['feedid']));
			
	}elseif(isset($_GET['article'])){
		
		mysql_query("INSERT INTO `feeds_read` (`article_id`, `user_id`) VALUES (".intval($_GET['article']).", ".intval($user_id).")");
		
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
	echo mysql_error();
		$json = array();
		$json['unread'] = array();
		while ($row = mysql_fetch_assoc($all_qry)) {
			$json['unread'][$row['feed_id']] = intval($row['c']);
		}        
		$json['unread']['all'] = array_sum($json['unread']);
		echo json_encode($json); 
		exit; 
	}
} else {
	header('Location: index.php');
	exit;       
}
?>                                           
