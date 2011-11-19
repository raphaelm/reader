<?php 
require_once('setlocale.php');
	include_once('functions.inc.php'); 
?>
<!DOCTYPE html>
<html>
  <head>
      <title><?php echo $title ?></title>
      <meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<?php
			if(is_mobile())
				echo '<link rel="stylesheet" type="text/css" href="stylelm.css" />
				  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />';
			else
				echo '<link rel="stylesheet" type="text/css" href="style.css" />';
		?>
	  <script type="text/javascript" src="jquery.js"></script>
      <meta http-equiv="Content-Style-Type" content="text/css" />
  </head>
  <body>
