<?php
session_start();
require_once 'includes/dbconnect.php';
if ($user_id) {
	require 'includes/application_header.php';
	require 'includes/application_navi.php';
	echo '<div id="right-col">';
	echo '<div id="wrap" class="reader-field"><h2>'._('Startseite').'</h2>';
	
	$dq = mysql_query("SELECT COUNT(`feedid`) as c, (SELECT COUNT(id) FROM feeds WHERE lastupdate < ".(time()-1000).") as c2 FROM `view_feed_subscriptions` WHERE `userid` =". $user_id. " AND lastupdate < ".(time()-1000));
	$d = mysql_fetch_object($dq);
	if($d->c > 7 or $d->c2 > 13){
		echo '<p class="error">
					'._('Wir leiden derzeit leider unter einem technischen Problem und hoffen, dass dieses bald behoben werden kann.').'
				</p>';
	}

	if(!isset($_SESSION['add_csrf_hashes'])) $_SESSION['add_csrf_hashes'] = array();
	$s = sha1(mt_rand());
	$_SESSION['add_csrf_hashes'][] = $s;
	printf('<a class="dashboardbox fullwidth" href="all.php">'._('%s neue Beiträge in deinen Feeds').'</a>', '<strong class="unreadcount_zero_all">'.$unread["all"].'</strong>');
	printf('<a class="dashboardbox left stickybox" href="sticky.php">'._('%s Beiträge auf deiner Merkliste').'</a>', '<strong class="unreadcount_zero_sticky">'.$sticky.'</strong>');
	echo '<div class="dashboardbox right addfeed" rel="'.$s.'">'._('Neuen Feed abonnieren').'</div>';
	
	echo '<div style="clear: both;"></div>';
	
	if(mysql_num_rows(mysql_query('SELECT * FROM feeds_subscription WHERE userid = '.$user_id)) <= 1){
		echo '<a class="dashboardbox fullwidth" href="import.php">'._('Abonnements aus einem anderen Feedreader (z.B. Google Reader) importieren').'</a>';
	}
	

	
	echo '</div></div>
		<div id="right-gap"></div>
		<div class="clear"></div>'; 
	require 'includes/application_footer.php';
	
} else {
	header('Location: index.php');
	exit;       
}
