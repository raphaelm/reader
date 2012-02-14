<?php
if(is_mobile()){
	echo '<div id="header"></div>
	<div id="content">
		<form id="login" action="index.php?mobile=true" method="POST">
			<label for="username">'._('Nutzername').':</label><br />
			<input class="inputl" type="text" id="username" name="username" value="" />
			<label for="password">'._('Passwort').':</label><br />
			<input class="inputl" type="password" id="password" name="password" value="" />
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
			<input class="buttonl" type="submit" id="loginbtn" value="<?php echo _('Anmelden') ?>" /> 
		</form>
		<a href="register.php" class="buttonl"><?php echo _('Registrieren') ?></a>
		<div class="clear"></div>
	</div>
	<p class="footer"><a href="lostpw.php"><?php echo _('Passwort vergessen?') ?></a></p>
	<?php
	require 'footl.php';
}
