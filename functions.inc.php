<?php
function utf_correct($str) {
	if (mb_detect_encoding($str, 'UTF-8, ISO-8859-1') != 'UTF-8') { 
		$str = utf8_encode($str);
	}
	return $str;
}

function urljoin($absolute, $relative) {
	$p = parse_url($relative);
	if($p["scheme"])return $relative;
	
	extract(parse_url($absolute));
	
	$path = dirname($path); 

	if($relative{0} == '/') {
		$cparts = array_filter(explode("/", $relative));
	}
	else {
		$aparts = array_filter(explode("/", $path));
		$rparts = array_filter(explode("/", $relative));
		$cparts = array_merge($aparts, $rparts);
		foreach($cparts as $i => $part) {
			if($part == '.') {
				$cparts[$i] = null;
			}
			if($part == '..') {
				$cparts[$i - 1] = null;
				$cparts[$i] = null;
			}
		}
		$cparts = array_filter($cparts);
	}
	$path = implode("/", $cparts);
	$url = "";
	if($scheme) {
		$url = "$scheme://";
	}
	if($user) {
		$url .= "$user";
		if($pass) {
			$url .= ":$pass";
		}
		$url .= "@";
	}
	if($host) {
		$url .= "$host/";
	}
	$url .= $path;
	return $url;
}

function error($msg){
	echo '<p>'.$msg.'</p>';
}
function fetch_feedurl($url) {
	$uri = parse_url($url);
	$ip = gethostbyname($uri['host']);
	if($ip == false or $ip == '62.75.159.223') {
		$nsl = shell_exec('nslookup '.escapeshellarg($uri['host']));
		if(strpos($nsl, 'NXDOMAIN') !== false) return false;
	}
	if($uri['scheme'] == 'https')
		$fp = @fsockopen('ssl://'.$ip, ((intval($uri['port']) > 0) ? $uri['port'] : 443), $err, $errn, 20);
	else
		$fp = @fsockopen($ip, ((intval($uri['port']) > 0) ? $uri['port'] : 80), $err, $errn, 20);
	if (!$fp) {
		return false;
	} else {
		$path = "";
		if(isset($uri['path']) && !empty($uri['path'])) $path .= $uri['path'];
		if(isset($uri['query']) && !empty($uri['query'])) $path .= "?".$uri['query'];
		$addheader = "";
		if(isset($uri['user']) && !empty($uri['user']) && isset($uri['pass']) && !empty($uri['pass']))
			$addheader .= "Authorization: Basic ".base64_encode($uri['user'].':'.$uri['pass'])."\r\n";
		if($path=="") $path="/";
		fwrite($fp, "GET $path HTTP/1.0\r\nHost: ".$uri['host']."\r\nUser-Agent: geeksfactory-reader/1.0$addheader\r\n\r\n");
		stream_set_timeout($fp, 10);
		$res = "";
		while(!feof($fp)){
			$new = fread($fp, 128);
			$res .= $new;
			if(strpos($new, '</head>') !== false) break;
			if(strpos($new, '<feed') !== false OR strpos($new, '<channel') !== false) break;
		}
		fclose($fp);
		if(strpos($res, '<feed') !== false OR strpos($res, '<channel') !== false){
			return $url;
		}
		$locsearch = preg_match('#Location: ([^ \r\n]*)#i', substr($res, 0, strpos($res, "\r\n\r\n")), $treffer);
		if($locsearch > 0){
			return fetch_feedurl($treffer[1]);
		}
		
		if(substr($res, 0, 10) == 'HTTP/1.1 4' || substr($res, 0, 10) == 'HTTP/1.1 3') {
			return false;
		}
		
		$htmlsearch = preg_match('#<link(([^>]*type=["\']application/(rss|atom)\+xml["\'][^>]*href=["\']([^"\']+)["\'][^>]*)|(<link[^>]*href=["\']([^"\']+)["\'][^>]*type=["\']application/(rss|atom)\+xml["\'][^>]*))>#i', $res, $treffer);
		if($htmlsearch > 0){
			return urljoin($url, htmlspecialchars_decode($treffer[4]));
		}
	}
	return $url;
}
function fetch_feedtitle($url) {
	if(!$url) return false;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	$ct = curl_exec($ch);
	$ct = substr($ct, 0, (strpos($ct, '<item') !== false ? strpos($ct, '<item') : strpos($ct, '<entry') ));
	curl_close($ch);
	
	$c = preg_match('#<title[^>]*>([^<]+)</title>#i', $ct, $treffer);
	
	if($c != 1){
		$c = preg_match('#<title[^>]*><!\[CDATA\[([^<]+)\]\]></title>#i', $ct, $treffer);
	}else return $treffer[1];
	
	if($c != 1){
		$c = preg_match('#<link[^>]*>([^<]+)</link>#i', $ct, $treffer);
	}else return $treffer[2];
	
	if($c !== 1){
		return false;
	}else return $treffer[1];
	
}
function is_mobile(){
	if(isset($_GET['mobile'])){
		if($_GET['mobile'] == 'false')
			return false;
		else
			return true;
	}
	if(defined('IS_MOBILE')) return true;
	if(isset($_GET['mobile'])) return true;
	require_once('lib/mobile_device_detect.php');
	return mobile_device_detect(true,false,true,true,true,true,true,false,false);
}
