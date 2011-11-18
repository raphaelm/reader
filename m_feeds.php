<?php
define('IS_MOBILE', true);
session_start();
if (isset($_SESSION['loggedin_as'])) {
	if (empty($_GET["feedid"])) {
		header('Location: m_all.php?mobile=true'); exit;
	}
	require_once("dbconnect.php");
	require_once("functions.inc.php");
	include('headerm.php');
	if (!empty($_GET["feedid"])) {
		$is_sub = mysql_query("SELECT `feeds`.`name` FROM `feeds_subscription` INNER JOIN `feeds` ON `feeds`.`id` = `feeds_subscription`.`feedid` WHERE `feedid` = ". intval(($_GET["feedid"])). " AND `userid` =". $_SESSION['loggedin_as']); 
		if (mysql_num_rows($is_sub) == 1) {
			$feed = mysql_fetch_assoc($is_sub);
			?>
			<div id="wrap">
			<div id="topbar">
				<a href="m_all.php"><img src="images/gfr.gif" alt="geek's factory reader" /></a> <?php selarea(utf_correct(($feed['alias']) ? $feed['alias'] : $feed['name'])); ?>
			</div>
			<p id="subnav">
				<?php if(!isset($_GET['show']) || $_GET['show'] == 'unread') echo '<strong>Ungelesene Einträge</strong> &middot; '; else echo '<a href="?feedid='.intval($_GET['feedid']).'&show=unread">Ungelesene Einträge</a> &middot; ';
				if(isset($_GET['show']) && $_GET['show'] == 'all') echo '<strong>Alle Einträge</strong>'; else echo '<a href="?feedid='.intval($_GET['feedid']).'&show=all">Alle Einträge</a>';
				?><br />
				<a href="markasread.php?feedid=<?php echo intval($_GET['feedid']); ?>&mobile=true">Alles in diesem Feed als gelesen markieren</a>
			</p>
			<?php
			$lasttimestamp = mysql_query("SELECT `timestamp`
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
						`feed_id` = ". intval(($_GET["feedid"])). " 
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
						(SELECT
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
						`feed_id` = ". intval(($_GET["feedid"])). " 
						".(
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
						AND timestamp >= ".intval($lasttimestamp)."
					ORDER by 
						`timestamp` desc"); 
			if($lasttimestamp < 1) $lasttimestamp = 0;
			echo '<script type="text/javascript">
						var lasttimestamp = '.$lasttimestamp.';
					</script>';
			if(mysql_num_rows($entries_qry) == 0){
				echo '<p class="info">
							Dieser Feed besitzt keine '.((!isset($_GET['show']) || $_GET['show'] == 'unread') ? 'ungelesenen ' : '').'Einträge. Wenn du ihn gerade erst aboniert hast, kann es bis zu fünf Minuten dauern, bis hier Einträge erscheinen. Außerdem werden keine Einträge angezeigt, die älter als 30 Tage sind.
						</p>';
			}
			while ($row = mysql_fetch_assoc($entries_qry)) {
				echo '<div id="article_'.$row["article_id"].'"'.($row["read_status"] == 0 ? ' class="unreadarticle"' : ' class="readarticle'.(($row["sticky"] == 1) ? ' sticky' : '').'"').'>';
				echo '<a href="javascript:void(0);" class="titlelink" onclick="togglearticle('.$row["article_id"].')">'. utf_correct($row["title"]). '</a>';
				echo '<em>'. date("d.m.Y". " - ". "H:i", $row["timestamp"]). '</em>';
				if($row["sticky"] == 1) echo ' &middot; <a href="javascript:unsticky('.$row["article_id"].');" class="stickylink">nicht merken</a>';
				else echo ' &middot; <a href="javascript:sticky('.$row["article_id"].');" class="stickylink">merken</a>';
				echo '<br /><div class="sum"><a href="'.$row["articleurl"].'" target="_blank">zum Originalbeitrag</a>';
				echo '<br />'. utf_correct(gzuncompress($row["summary"])). '</div></div>';
			} 
		} else {
			echo '<p class="error">Du abonnierst diesen Feed nicht.</p>';
		}   
	}
	echo "<a href='javascript:loadmore();' class='loadmore'>Mehr laden</a>";
	echo '</div>';
	include('footl.php');
} else {
	header('Location: index.php'); exit;       
}
?>

