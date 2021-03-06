<?php
if(defined('SETLOCATE_INCLUDED') or !$db_connection)
	return;
else
	define('SETLOCATE_INCLUDED', true);
	
require_once 'includes/dbconnect.php';
if ($user_id and isset($dbhostname)) {
	$q = mysql_query('SELECT locale FROM user WHERE id = '.intval($user_id));
	$r = mysql_fetch_object($q);
	$l = $r->locale;
}

if(isset($l) and in_array($l, $locales)){
	$locale = $l;
}elseif(isset($_GET['locale']) and in_array($_GET['locale'], $locales)){
	$locale = $_GET['locale'];
	setcookie('locale', $locale, time()+3600*24*365);
}elseif(isset($_COOKIE['locale']) and in_array($_COOKIE['locale'], $locales)){
	$locale = $_COOKIE['locale'];
}elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
	$firstfive = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5);
	$firsttwo = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	if(in_array($firstfive, $locales))
		$locale = $firstfive;
	elseif(in_array($firsttwo, $locales))
		$locale = $firsttwo;
	elseif(isset($localemappings[$firsttwo]))
		$locale = $localemappings[$firsttwo];
}

putenv('LC_ALL='.$locale);
setlocale(LC_ALL, $locale);
bindtextdomain('reader', './i18n');
textdomain('reader');
bind_textdomain_codeset('reader', 'utf-8');
