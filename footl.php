
<?php
if(is_mobile()){
	 echo "<div style='text-align: center;'>
		<a href='?'>"._("Zur Desktop-Version")."</a>
	</div>";
}else{
	if(!isset($_SESSION['loggedin_as'])){
		?>
			<div id="footer">
				<p>
					<?php
					$locs = array();
					foreach ($localenames as $loc => $name) {
						if($loc == $locale) continue;
						$locs[] = '<a href="?locale='.$loc.'">'.$name.'</a>';
					}
					echo join(" &middot; ", $locs);
					?>
				</p>
				<p>geek's factory reader &ndash; &copy; 2011 <a href="http://www.geeksfactory.de">geek's factory</a><br />
				<a href="http://git.geeksfactory.de/reader.git" target="_blank">get the source!</a></p>
				<p><a href='?mobile=true'><?php echo _("Zur mobilen Version"); ?></a></p>
			</div>
		<?php
	}
}
?>
	
    </body>
</html>
