<?php
if(is_mobile()){
	 echo "<div style='text-align: center;'>
		<a href='?'>"._("Zur Desktop-Version")."</a>
	</div>";
}else{
	if(!isset($_SESSION['loggedin_as']))
		echo "<p style='text-align: center;'>
			<a href='?mobile=true'>"._("Zur mobilen Version")."</a>
		</p>";
	
	/* $url = 'm_all.php';
	else $url = '?mobile=true';*/
}
?>
    </body>
</html>
