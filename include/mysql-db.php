<?php




function check_login($user, $pass){
	global $mysql_con, $mysql_prefix, $pass_salt;
	
	$user = mysql_real_escape_string($user, $mysql_con);
	$pass = md5($pass_salt . $pass);
	
	$res = mysql_query(
			"SELECT `id`, `name`, `role` FROM `{$mysql_prefix}users`
				WHERE `name` = '$user' 
				  AND `pass` = '$pass'
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







function user_exists($name){
	global $mysql_con, $mysql_prefix;
	
	$name = mysql_real_escape_string($name, $mysql_con);
	
	$res = mysql_query(
				"SELECT 1 FROM `{$mysql_prefix}users`
					WHERE LOWER(`name`) = LOWER('$name')
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
				"SELECT `id`, `name`, `role` FROM `{$mysql_prefix}users`", 
				$mysql_con);
	
	if(!$res) return false;
	
	while($row = mysql_fetch_assoc($res)){
		$users[] = $row;
		}
	
	mysql_free_result($res);
	
	return $users;
	}







function add_user($name, $pass, $role='read'){
	global $mysql_con, $mysql_prefix, $pass_salt;
	
	$user = mysql_real_escape_string($user, $mysql_con);
	$pass = md5($pass_salt . $pass);
	
	$res = mysql_query(
			"INSERT INTO `{$mysql_prefix}users`
				(`name`,`pass`,`role`)
				VALUES
				('$name', '$pass', '$role')", 
			$mysql_con);
	
	
	if(!$res || !mysql_insert_id($mysql_con)) return false;
	
		
	return true;
	}








function del_user($id){
	global $mysql_con, $mysql_prefix, $pass_salt;
	
	$id = (int)$id;
	
	$res = mysql_query(
			"DELETE FROM `{$mysql_prefix}users`
				WHERE `id`='$id'
				LIMIT 1",
			$mysql_con);
	
	
	if(!$res || !mysql_affected_rows($mysql_con)) return false;
	
	return true;
	}








function user_role($id, $role=false){
	global $mysql_con, $mysql_prefix, $pass_salt;
	
	$id = (int)$id;
	
	if($role === false){
		$res = mysql_query(
				"SELECT `role` FROM `{$mysql_prefix}users`
					WHERE `id`='$id'
					LIMIT 1", 
				$mysql_con);
		
		if(!$res || !mysql_num_rows($res)) return false;
		
		$row = mysql_fetch_assoc($res);
		
		mysql_free_result($res);
		
		return $row['role'];
		}
	
	$role = mysql_real_escape_string($role, $mysql_con);
	
	$res = mysql_query(
			"UPDATE `{$mysql_prefix}users`
				SET `role`='$role'
				WHERE `id`='$id'
				LIMIT 1", 
			$mysql_con);
	
	if(!$res || !mysql_affected_rows($mysql_con)) return false;
	return true;
	}








function user_pass($id, $pass){
	global $mysql_con, $mysql_prefix, $pass_salt;
	
	$id = (int)$id;
	$pass = md5($pass_salt . $pass);
	
	$res = mysql_query(
			"UPDATE `{$mysql_prefix}users`
				SET `pass`='$pass'
				WHERE `id`='$id'
				LIMIT 1", 
			$mysql_con);
	
	if(!$res || !mysql_affected_rows($mysql_con)) return false;
	return true;
	}







$mysql_con = mysql_connect($mysql_host, $mysql_user, $mysql_pass);

if(!$mysql_con){
	die('Unable to connect to mysql server.');
	}


if(!mysql_select_db($mysql_db ,$mysql_con)){
	die('Could not select mysql database.');
	}
	




?>