<?php
if(is_mobile()){
	echo '<div id="header"></div>
	<div id="content">
		<form id="login" action="index.php?mobile=true" method="POST">
			<label for="username">Nutzername:</label><br />
			<input class="inputl" type="text" id="username" name="username" value="" />
			<label for="password">Passwort:</label><br />
			<input class="inputl" type="password" id="password" name="password" value="" />
			<input class="buttonl" type="submit" value="Anmelden" /> 
		</form>
		<a href="register.php?mobile=true" class="buttonl">Registrieren</a>
		<div class="clear"></div>
	</div></div>';
}else{
	?>
	<div id="header"></div>
	<div id="content">
		<form id="login" action="index.php" method="POST">
			<input class="inputl" type="text" name="username" id="username" value="" onfocus="if(this.value == 'Nutzername') this.value = ''" onblur="if(this.value == '') this.value = 'Nutzername'" />
			<input class="inputl" type="password" name="password" id="password" value="" onfocus="if(this.value == 'Passwort') this.value = ''" onblur="if(this.value == '') this.value = 'Passwort'" />
			<input class="buttonl" type="submit" id="loginbtn" value="Anmelden" /> 
		</form>
		<a href="register.php" class="buttonl">Registrieren</a>
		<div class="clear"></div>
	</div>
	<script type="text/javascript">
		$(document).ready(function(){
			window.setTimeout(function(){
			if($("#username").val() == ""){
				$("#username").val("Nutzername")
			}else{
				$("#username").focus();
				$("#password").focus();
				$("#loginbtn").focus();
				$("#username").css("background", " url(images/round-big-f.png) no-repeat");
			}
			if($("#password").val() == "")
				$("#password").val("Passwort")
			}, 150);
		});
	</script>
	<div id="footer">
		<p><a href="lostpw.php">Passwort vergessen?</a></p>
		<p>geek's factory reader - on geeksfactory.de</p>
	</div>
	<?php
}
