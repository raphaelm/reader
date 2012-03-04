<?php
if(is_mobile()){
	echo '<div id="header"></div>
	<div id="content">
		<form id="login" action="index.php?mobile=true" method="POST">
			<label for="username">'._('Nutzername').':</label><br />
			<input class="inputl" type="text" id="username" name="username" value="" />
			<label for="password">'._('Passwort').':</label><br />
			<input class="inputl" type="password" id="password" name="password" value="" />
			<div class="checkboxl"><input type="checkbox" name="long" value="yes" id="long" /> <label for="long">'._('Eingeloggt bleiben').'</label></div>
			<input class="buttonl" type="submit" value="'._('Anmelden').'" /> 
		</form>
		<a href="register.php?mobile=true" class="buttonl">'._('Registrieren').'</a>
		<div class="clear"></div>
	</div></div>';
}else{
	?>
	<div id="header"></div>
	<div id="content" class="gradient">
		<form id="login" action="index.php" method="POST">
			<input class="inputl" type="text" name="username" id="username" value="" placeholder="<?php echo _('Nutzername') ?>" />
			<input class="inputl" type="password" name="password" id="password" value="" placeholder="<?php echo _('Passwort') ?>" />
			<div class="checkboxl"><input type="checkbox" name="long" value="yes" id="long" /> <label for="long"><?php echo _('Eingeloggt bleiben') ?></label></div>
			<input class="buttonl" type="submit" id="loginbtn" value="<?php echo _('Anmelden') ?>" /> 
		</form>
		<a href="register.php" class="buttonl"><?php echo _('Registrieren') ?></a>
		<div class="clear"></div>
	</div>
	<p class="footer"><a href="lostpw.php"><?php echo _('Passwort vergessen?') ?></a></p>
	<?php
	require 'includes/login_footer.php';
}
