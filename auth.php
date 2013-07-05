<?php

// Nobody should run this file on its own.
if(!defined('INSPY') || INSPY != true){
	header('Location: ./');
	die();
	}

require('config.php');
require('mysql-db.php');


session_start();


// First page visit. Fill the fresh session.
if(!isset($_SESSION['logged_in'])){
	$_SESSION['logged_in'] = false;
	$_SESSION['sec_token'] = base64_encode(md5(uniqid('8et41sdf87er', true)));
	}

	

// verify security token for all actions.
if(isset($_POST['sec_token']) && $_POST['sec_token'] == $_SESSION['sec_token']){
	define('SPY_SEC', true);
}else{
	define('SPY_SEC', false);
	}
	

// handle login attempts
if(SPY_SEC && isset($_POST['login'])){

	
	if(check_login($_POST['user'], $_POST['pass'])){
		// login success
		$_SESSION['logged_in'] = true;
		
		// generate a new token when someone logs in
		$_SESSION['sec_token'] = base64_encode(md5(uniqid('5sdf66g4d6f8g', true)));
		
		// Let's redirect to the home page.
		header('Location: ./');
		die();
		}
	
	}



if(!$_SESSION['logged_in']){
	
	// if the request came from an ajax page...
	if(defined('SPY_JSON')){
		$json_out = array('success' => 0, 'error' => 'You are not logged in.');
		die(json_encode($json_out));
		}

// Otherwise....

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link type="text/css" rel="stylesheet" media="screen" href="reset.css">
	<link type="text/css" rel="stylesheet" media="screen" href="style.css">
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	<title>Login - Session Spy</title>
	<script type="text/javascript">
		$(function(){
			$('#login form img').animate({height: '36px', width: '36px'});
			
			$('#submit').click(function(event){
				
				if(!$('#user').val()){
					event.preventDefault();
					$('#user-req').slideDown();
					}
				
				if(!$('#pass').val()){
					event.preventDefault();
					$('#pass-req').slideDown();
					}
				
				});
			});
	</script>
	
	<?php
		//if(isset($_POST['user']))
	?>
	<style type="text/css">
		
	</style>
</head>

<body id="login">
	<span class="session_spy">Session Spy</span>
	
	<div class="wrap">
	
		<h1>Login</h1>
		
		<form action="./" method="POST">
			
			<?php if(isset($_POST['login']) && ($_POST['user'] || $_POST['pass'])){?>
				<div class="req" style="display:block;">Incorrect Username/Password</div>
			<?php } ?>
			
			<div class="req" id="user-req">Username is required*</div>
			<input type="text" name="user" id="user" placeholder="Username" /><br />
			
			<div class="req" id="pass-req">Password is required*</div>
			<input type="password" name="pass" id="pass" placeholder="Password" /><br />
			
			<img src="login-lock.png" />
			
			<input type="hidden" name="sec_token" value="<?php echo $_SESSION['sec_token']; ?>" />
			
			<input type="submit" value="Login" name="login" id="submit" />
			<br clear="both" />
			
		</form>
		
	</div>
	
</body>
</html>
<?php
die();
}
?>