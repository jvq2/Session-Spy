<?php

// Nobody should run this file on its own.
if(!defined('INSPY') || INSPY !== true){
	header('Location: ./');
	die();
	}


session_start();


// First page visit. Fill the fresh session.
if(!isset($_SESSION['sspy_logged_in'])){
	$_SESSION['sspy_logged_in'] = false;
	$_SESSION['sspy_sec_token'] = base64_encode(md5(uniqid('8et41sdf87er', true)));
	}

	

// verify security token for all actions.
if(isset($_REQUEST['sec_token']) && $_REQUEST['sec_token'] == $_SESSION['sspy_sec_token']){
	define('SPY_SEC', true);
}else{
	define('SPY_SEC', false);
	}



// Logout
if(SPY_SEC && isset($_REQUEST['logout'])){

	if($_SESSION['sspy_logged_in']){
		session_unset();
		}
	
	// always return json true
	if(defined('SPY_JSON')){
		$json_out = array('success' => 1);
		die(json_encode($json_out));
		}
	
	header('Location: ./');
	die();
	}



// handle login attempts
if(SPY_SEC && isset($_POST['login'])){

	$u = check_login($_POST['user'], $_POST['pass']);
	if($u){
		
		// login success
		$_SESSION['sspy_logged_in'] = true;
		
		// save user info in session
		$_SESSION['sspy_user'] = $u;
		
		// generate a new token when someone logs in
		$_SESSION['sspy_sec_token'] = base64_encode(md5(uniqid('5sdf66g4d6f8g', true)));
		
		
		// if ajax request
		if(defined('SPY_JSON')){
			$json_out = array('success' => 1, 'sec_token' => $_SESSION['sspy_sec_token']);
			die(json_encode($json_out));
			}
		
		
		// Let's redirect to the home page.
		header('Location: ./');
		die();
		}
	
	}



if(!$_SESSION['sspy_logged_in']){
	
	// if the request came from an ajax page...
	if(defined('SPY_JSON')){
		$json_out = array('success' => 0, 'error' => 'You are not logged in.');
		die(json_encode($json_out));
		}

	// Otherwise....
	require('login-form.php');

	// If not logged in, display login page, then die.
	// This script is included in other files, so die() is needed to
	// prevent further unauthorized code execution in those pages.
	die();
	
	
}else{ // someone is logged in
	
	if($_SESSION['sspy_user']['role'] == 'admin'){
		define('SPY_ADMIN', true);
	}else{
		define('SPY_ADMIN', false);
		}
	}
	
	
?>