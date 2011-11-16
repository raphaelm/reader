<?php
session_start();
unset($_SESSION['loggedin_as']);
header('Location: index.php'.(isset($_REQUEST['mobile']) ? '?mobile=true' : '').''); exit;   
