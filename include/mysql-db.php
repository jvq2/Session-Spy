<?php




function check_login($user, $pass){
	global $mysql_con, $mysql_prefix, $pass_salt;
	
	$user = mysql_real_escape_string($user);
	$pass = md5($pass_salt . $pass);
	
	$res = mysql_query(
			"SELECT `id`, `name`, `role` FROM `". $mysql_prefix ."users`
				WHERE `name` = '".$user."' 
				  AND `pass` = '".$pass."'
				LIMIT 1", 
			$mysql_con);
	
	
	if(!$res) return false;
	
	if(!mysql_num_rows($res)){
		mysql_free_result($res);
		return false;
		}
	$user = mysql_fetch_assoc($res);
	
	mysql_free_result($res);
	return $user;
	}


function check_name($name){
	global $mysql_con, $mysql_prefix;
	
	$name = mysql_real_escape_string($name);
	
	$res = mysql_query(
				"SELECT 1 FROM `". $mysql_prefix ."users`
					WHERE LOWER(`name`) = LOWER('".$name."')
					LIMIT 1",
					$mysql_con);
	
	if(!$res) return false;
	
	if(mysql_num_rows($res) == 1){
		mysql_free_result($res);
		return true;
		}
	
	mysql_free_result($res);
	return false;
	}



function list_users(){
	global $mysql_con, $mysql_prefix;
	
	$res = mysql_query(
				"SELECT `id`, `name`, `role` FROM `". $mysql_prefix ."users`", 
				$mysql_con);
	
	if(!$res) return false;
	
	while($row = mysql_fetch_assoc($res)){
		$users[] = $row;
		}
	
	mysql_free_result($res);
	
	return $users;
	}








$mysql_con = mysql_connect($mysql_host, $mysql_user, $mysql_pass);

if(!$mysql_con){
	die('Unable to connect to mysql server.');
	}


if(!mysql_select_db($mysql_db ,$mysql_con)){
	die('Could not select mysql database.');
	}
	




?>