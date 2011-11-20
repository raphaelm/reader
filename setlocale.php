<?php
require_once('config.inc.php');
if (isset($_SESSION['loggedin_as']) and isset($dbhostname)) {
	require('dbconnect.php');
	$q = mysql_query('SELECT locale FROM user WHERE id = '.intval($_SESSION['loggedin_as']));
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
}


putenv('LC_ALL='.$locale);
setlocale(LC_ALL, $locale);
bindtextdomain('reader', './i18n');
textdomain('reader');
bind_textdomain_codeset('reader', 'utf-8');
