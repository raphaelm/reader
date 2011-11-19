<?php
session_start();
if (isset($_SESSION['loggedin_as'])) {
	if (empty($_GET["feedid"])) {
		exit;
	}
	require_once("dbconnect.php");
	include_once('functions.inc.php');
	
	if (!empty($_GET["feedid"])) {
		$is_sub = mysql_query("SELECT `feeds`.`name` FROM `feeds_subscription` INNER JOIN `feeds` ON `feeds`.`id` = `feeds_subscription`.`feedid` WHERE `feedid` = ". intval(($_GET["feedid"])). " AND `userid` =". $_SESSION['loggedin_as']); 
		if (mysql_num_rows($is_sub) == 1) {
			$feed = mysql_fetch_assoc($is_sub);
			
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
						timestamp < ".intval($_GET['lasttimestamp'])."	
						AND `feed_id` = ". intval(($_GET["feedid"])). "	
					ORDER by 
						timestamp DESC	
					LIMIT 30");
			while($r = mysql_fetch_object($lasttimestamp)) {$last = $r;}
			$lasttimestamp = $last->timestamp;
			
			$entries_qry = mysql_query("SELECT 
						`article_id`,
						`title`, 
						`url`, 
						`timestamp`, 
						(SELECT COUNT(*) FROM sticky s WHERE user_id = ".$_SESSION['loggedin_as']." AND s.article_id = `feeds_entries`.article_id) as `sticky`,
						`summary`, 
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
					WHERE 
						`feed_id` = ". intval(($_GET["feedid"])). " ".(
						(!isset($_GET['show']) || $_GET['show'] == 'unread') ?
							"AND 0 = (SELECT
								COUNT(`article_id`)
							FROM
								`feeds_read`
							WHERE
								`user_id` = ". $_SESSION['loggedin_as']. "
								AND
								`feeds_read`.`article_id` = `feeds_entries`.`article_id`
							)"
						: '')."
					AND timestamp < ".intval($_GET['lasttimestamp'])."
					AND timestamp >= ".intval($lasttimestamp)."
				ORDER by 
					`timestamp` desc"); 
			 echo '<script type="text/javascript">
					lasttimestamp = '.$lasttimestamp.';
				 </script>';
			if($lasttimestamp < 1) $lasttimestamp = 0;
			 
			if(mysql_num_rows($entries_qry) == 0) die('<!-- NOTHING MORE -->');
			
			while ($row = mysql_fetch_assoc($entries_qry)) {
				echo '<div id="article_'.$row["article_id"].'"'.($row["read_status"] == 0 ? ' class="unreadarticle"' : ' class="readarticle'.(($row["sticky"] == 1) ? ' sticky' : '').'"').'>';
				echo '<a '.(isset($_GET['mobile']) ? 'onclick="togglearticle('.$row["article_id"].')" href="javascript:void(0);' : 'target="_blank" href="'. $row["articleurl"]). '" class="titlelink">'. utf_correct($row["title"]). '</a>';
				echo (isset($_GET['mobile']) ? '' : '<br />').'<em>'. date(_("d.m.Y - H:i"), $row["timestamp"]). '</em>';
				if($row["sticky"] == 1) echo ' &middot; <a href="javascript:unsticky('.$row["article_id"].');" class="stickylink">'._('nicht merken').'</a>';
				else echo ' &middot; <a href="javascript:sticky('.$row["article_id"].');" class="stickylink">'._('merken').'</a>';
				echo '<br /><div class="sum"'.(isset($_GET['mobile']) ? ' style="display:none"><a href="'.$row["articleurl"].'" target="_blank">'._('zum Originalbeitrag').'</a><br />' : '>' );
				echo utf_correct(gzuncompress($row["summary"])). '</div></div>';
			} 
		} else {
			echo '<p class="error">'._('Du abonnierst diesen Feed nicht.').'</p>';
		}
	}
} else {
	echo json_encode(array('error' => 'not logged in')); exit;       
}
?>
                        
