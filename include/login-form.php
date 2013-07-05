<?php

// Nobody should run this file on its own.
if(!defined('INSPY') || INSPY !== true){
	header('Location: ./');
	die();
	}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link type="text/css" rel="stylesheet" media="screen" href="css/reset.css">
	<link type="text/css" rel="stylesheet" media="screen" href="css/style.css">
	<script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
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
			
			<img src="images/login-lock.png" />
			
			<input type="hidden" name="sec_token" value="<?php echo $_SESSION['sec_token']; ?>" />
			
			<input type="submit" value="Login" name="login" id="submit" />
			<br clear="both" />
			
		</form>
		
	</div>
	
</body>
</html>