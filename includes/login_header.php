<?php 
require_once 'includes/dbconnect.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html>
  <head>
		<title><?php echo $title ?></title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<?php
			if(is_mobile())
				echo '<link rel="stylesheet" type="text/css" href="style_mobile_login.css" />
				  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />';
			else
				echo '<link rel="stylesheet" type="text/css" href="style_login.css" />';
		?>
		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/jquery.placeholder.min.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){
				$('input, textarea').placeholder();
			});
		</script>
		<!--[if gte IE 9]>
			<style type="text/css">
				.gradient {
					filter: none;
				}
			</style>
		<![endif]-->
      <meta http-equiv="Content-Style-Type" content="text/css" />
  </head>
  <body>
	<div class="langselect">
		<?php
		if(is_mobile()){
			foreach ($locales as $loc) {
				echo '<a href="?locale='.$loc.'"><img src="i18n/flags/'.$loc.'.gif" alt="'.$loc.'" /></a> ';
			}
		}else{
			foreach ($locales as $loc) {
				echo '<a href="?locale='.$loc.'"><img src="i18n/flags/'.$loc.'.gif" alt="'.$loc.'" /></a> ';
			}
		}
		?>
	</div>
