<?php
$locale = 'de_DE';
$locales = array('de_DE', 'en_GB');
if(isset($_GET['locale']) and in_array($_GET['locale'], $locales)){
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
