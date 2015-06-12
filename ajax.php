<?php
	define('INSPY', true);
	define('SPY_JSON', true);
	require_once('./include/autoload.php');
	require_once('./include/common.php');
	require_once('./include/auth.php');
	
	
	

	$json_out = array('success' => 0);



	// only allow request that have past THE TESTS!!!!
	if(!SPY_SEC){
		$json_out['error'] = 'Security token not found.';
		die(json_encode($json_out));
	}




	function ax_search_list($item){
		global $ax_search_value, $prefix_n;
		
		return strpos($item, $ax_search_value, $prefix_n);
	}



	function hide_sec_token($sess){
		
		if(array_key_exists('sspy_sec_token', $sess)){
			$sess['sspy_sec_token'] = "[Hidden]";
		}
			
		return $sess;
	}


	function parse_data($data){
		
		$new_data = array('type' => gettype($data));
		
		switch($new_data['type']){
			
			case 'object':
				
				$name = get_class($data);
				
				$data = (array)$data;
				
				if($name == '__PHP_Incomplete_Class' && array_key_exists('__PHP_Incomplete_Class_Name', $data)){
					$name = $data['__PHP_Incomplete_Class_Name'];
					unset($data['__PHP_Incomplete_Class_Name']);
				}
				
				$new_data['value'] = array();
				$new_data['class'] = htmlspecialchars($name);
				
				$i = 0;
				
				foreach($data as $key => $val){
					$priv = 0;
					$prot = 0;
					
					$k = str_replace("\0". $name ."\0_", '', $key, $priv);
					
					$k = str_replace("\0*\0_", '', $k, $prot);
					
					$new_data['value'][$i] = parse_data($val);
					$new_data['value'][$i]['key'] = htmlspecialchars($k);
					$new_data['value'][$i]['flag'] = $priv?'private' : $prot?'protected' : 'public';
					
					$i++;
				}
				break;
				
			case 'array':
				
				$new_data['value'] = array();
				
				$i = 0;
				foreach($data as $key => $val){
					$new_data['value'][$i] = parse_data($val);
					$new_data['value'][$i]['key'] = htmlspecialchars($key);
					$i++;
				}
					
				break;
				
			case 'boolean':
				$new_data['value'] = $data;
				break;
				
			default: // other scalar types
				$new_data['value'] = htmlspecialchars($data);
				break;
		}
		
		return $new_data;
	}









	// No action is given
	if(!isset($_POST['action']) || !$_POST['action']){
		
		$json_out['error'] = 'Action is required';
		
		die(json_encode($json_out));
	}




	$action = $_POST['action'];



	switch($action){
		
		case 'del':
		
			if(!isset($_POST['sid']) || !$_POST['sid']){
				
				$json_out['error'] = 'Session ID is required';
				
				die(json_encode($json_out));
				}
				
			if(!SPY_ADMIN){
				
				$json_out['error'] = 'You must be an administrator to delete';
				
				die(json_encode($json_out));
				}
			
			$sid = $_POST['sid'];
			
			$json_out['session_id'] = $sid;
			
			// is this a valid session id
			if(!preg_match('/^[a-zA-Z0-9]+$/', $sid)){
				$json_out['error'] = 'Could not delete session. The specified session is invalid.';
				
				die(json_encode($json_out));
				}
				
			$save_path = session_save_path();
			
			// does this session exist
			if(!file_exists($save_path.'/sess_'.$sid)){
				$json_out['error'] = 'Could not delete session. The specified session does not exist.';
				
				die(json_encode($json_out));
				}
			
			// magic
			$r = unlink($save_path.'/sess_'.$sid);
			
			
			if(!$r){
				$json_out['error'] = 'Could not delete session';
				
				die(json_encode($json_out));
				}
			
			$json_out['success'] = 1;
			
			die(json_encode($json_out));
		
		
		
		case 'get':
		
			if(!isset($_POST['sid']) || !$_POST['sid']){
				
				$json_out['error'] = 'Session ID is required';
				
				die(json_encode($json_out));
				}
			
			// dont send this sessions cookie to the client
			//ini_set('session.use_cookies', '0');
			
			$sid = $_POST['sid'];
			
			$json_out['session_id'] = $sid;
			
			// is that a valid session id?
			if(!preg_match('/^[a-zA-Z0-9]+$/', $sid)){
				$json_out['error'] = 'The specified session is invalid.';
				
				die(json_encode($json_out));
				}
			
			
			$save_path = session_save_path();
			
			
			// does the session exist?
			if(!file_exists($save_path.'/sess_'.$sid)){
				$json_out['error'] = 'The specified session does not exist.';
				
				die(json_encode($json_out));
				}
			
			if(session_id() == $sid){
				$data = hide_sec_token($_SESSION);
				
				// magic
				$json_out['session'] = parse_data($data);
				
				// The process leaves a one item root node. Lets remove it.
				$json_out['session'] = $json_out['session']['value'];
				
				// I'm taking a note here.
				$json_out[/*huge*/'success'] = 1;
				
				die(json_encode($json_out));
				}
			
			// save old session to restore in a few lines
			$old_session = $_SESSION;
			$_SESSION = array();
			
			// read session data
			$s = file_get_contents(session_save_path().'/sess_'.$sid);
			
			// failure reading
			if($s === false){
				// make sure we dont loose our data in the event of an error
				$_SESSION = $old_session;
				
				$json_out['error'] = 'Could not open session';
				die(json_encode($json_out));
				}
			
			// Stupid PHP uses a special session encoding/decoding algorithm.
			// So we must use their session decoding and overwrite the current session.
			// We'll put it back in a second...
			
			if(!session_decode($s)){
				$_SESSION = $old_session;
				
				$json_out['error'] = 'Could not decode session '.var_export($_SESSION,true);
				die(json_encode($json_out));
				}
			
			// save read session data
			$data = $_SESSION;
			
			// restore previous session so that we dont lose our login
			$_SESSION = $old_session;
			
			// Keep sspy security tokens from view
			$data = hide_sec_token($data);
			
			// magic
			$json_out['session'] = parse_data($data);
			
			// The process leaves a one item root node. Lets remove it.
			$json_out['session'] = $json_out['session']['value'];
			
			// I'm taking a note here.
			$json_out[/*huge*/'success'] = 1;
			
			die(json_encode($json_out));
			
		
		
		
		case 'del_var':
			$_POST['var_type'] = 'DELETE';
			$_POST['value'] = 'UNUSED';
		case 'add_var':
		case 'edit':
		
			if(!isset($_POST['sid']) || !$_POST['sid']){
				
				$json_out['error'] = 'Session ID is required';
				
				die(json_encode($json_out));
				}
			
			if(empty($_POST['var_type'])){
				
				$json_out['error'] = 'Variable type is required';
				
				die(json_encode($json_out));
				}
			
			if(!isset($_POST['var_path']) || $_POST['var_path'] === ""){
				
				$json_out['error'] = 'Variable path is required';
				
				die(json_encode($json_out));
				}
			
			if(!isset($_POST['value'])){
				
				$json_out['error'] = 'Variable value is required';
				
				die(json_encode($json_out));
				}
			
			// dont send this sessions cookie to the client
			// NO LONGER NEEDED
			//ini_set('session.use_cookies', '0');
			
			$sid = $_POST['sid'];
			$var_path = $_POST['var_path'];
			$var_type = $_POST['var_type'];
			$value = $_POST['value'];
			
			
			$json_out['session_id'] = $sid;
			
			// is that a valid session id?
			if(!preg_match('/^[a-zA-Z0-9]+$/', $sid)){
				$json_out['error'] = 'The specified session is invalid.';
				
				die(json_encode($json_out));
				}
			
			
			$save_path = session_save_path();
			
			
			// does the session exist?
			if(!file_exists($save_path.'/sess_'.$sid)){
				$json_out['error'] = 'The specified session does not exist.';
				
				die(json_encode($json_out));
				}
			
			// Are we editing the current php session?
			$CUR_SESS = (session_id() == $sid);
			
			
			if(!$CUR_SESS){
				// session not already loaded
				// save old session to restore in a few lines
				$old_session = $_SESSION;
				$_SESSION = array();
				
				// read session data
				$s = file_get_contents(session_save_path().'/sess_'.$sid);
				
				// failure reading file
				if($s === false){
					// make sure we dont loose our data in the event of an error
					$_SESSION = $old_session;
					
					$json_out['error'] = 'Could not open session';
					die(json_encode($json_out));
					}
				
				// failure decoding session data ... stupid php
				if(!session_decode($s)){
					$_SESSION = $old_session;
					
					$json_out['error'] = 'Could not decode session (please report) $_SESSION: '.var_export($_SESSION,true);
					die(json_encode($json_out));
					}
				
			}
			
			if($var_path[0] == 'sspy_sec_token' || $var_path[0] == 'sspy_user' || $var_path[0] == 'sspy_logged_in'){
				$json_out['error'] = "Editing of session spy user info is not allowed.";
				
				die(json_encode($json_out));
			}
			
			$cur = &$_SESSION;
			$lvl = 1;
			$last_key = array_pop($var_path);
			$tcur = 'array';
			
			// Follow rabbit trail into session variables and get a handle for 
			//  the proper level
			foreach($var_path as $key){
				
				if($tcur == 'object'){
					//if(!property_exists($cur, $key)){
					if(!isset($cur->$key)){
						$json_out['error'] = "Property '$key' @ position $lvl does not exist";
						
						$json_out['dump'] = $cur->$key;
						
						die(json_encode($json_out));
					}else {
						
						$lvl++;
						//$cur = &$cur[$key];
						$cur = &$cur->$key;
						$tcur = gettype($cur);
						continue;
					}
					
				}else if(!isset($cur[$key])){
					$json_out['error'] = "Key '$key' @ position $lvl does not exist";
					
					die(json_encode($json_out));
					}
				
				$lvl++;
				$cur = &$cur[$key];
				$tcur = gettype($cur);
				}
			
			
			
			if($tcur == 'object'){
				$ake = isset($cur->$last_key);
			} else {
				$ake = array_key_exists($last_key, $cur);
			}
			
			if($action != 'add_var' && !$ake){
				$json_out['error'] = "That key does not exist. Perhaps the session changed since it was fetched. Try using the green refresh button.";
				
				die(json_encode($json_out));
			} elseif($action == 'add_var' && $ake) {
				$json_out['error'] = "A key by that name already exists. Please choose another name or edit the existing key.";
				
				die(json_encode($json_out));
			}
			
			
			
			// and finally, set the variable
			switch(strtolower($var_type)){
				case 'boolean':
					if($tcur == 'object'){
						$cur -> $last_key = (bool)$value;
					}else{
						$cur[$last_key] = (bool)$value;
					}
					break;
				case 'integer':
					if($tcur == 'object'){
						$cur -> $last_key = (int)$value;
					}else{
						$cur[$last_key] = (int)$value;
					}
					break;
				case 'string':
					if($tcur == 'object'){
						$cur -> $last_key = (string)$value;
					}else{
						$cur[$last_key] = (string)$value;
					}
					break;
				case 'double':
					if($tcur == 'object'){
						$cur -> $last_key = (double)$value;
					}else{
						$cur[$last_key] = (double)$value;
					}
					break;
				case 'null':
					if($tcur == 'object'){
						$cur -> $last_key = NULL;
					}else{
						$cur[$last_key] = NULL;
					}
					break;
				case 'array':
					if($tcur == 'object'){
						$cur -> $last_key = array();
					}else{
						$cur[$last_key] = array();
					}
					break;
					//}
				case 'delete':
					if($action == 'del_var'){
						if($tcur == 'object'){
							unset($cur -> $last_key);
						}else{
							unset($cur[$last_key]);
						}
						break;
					}
				case 'object':
					if($action == 'add_var'){
						
						if($tcur == 'object'){
							$cur -> $last_key = new $value;
						}else{
							$cur[$last_key] = new $value;
						}
						break;
					}
				default:
					$json_out['error'] = "Only strings, integers, doubles, booleans, and null can be edited.";
				
					die(json_encode($json_out));
			}
			
			
			if(!$CUR_SESS){
				// Save other sessions back to their files.
				$s = session_encode();
				
				// restore previous session so we don't lose our login
				$_SESSION = $old_session;
				
				if(!$s) {
					$json_out['error'] = "Could not save (encode) new session data.";
					
					die(json_encode($json_out));
				}
				
				if(file_put_contents(session_save_path().'/sess_'.$sid, $s) === false){
					$json_out['error'] = "Could not save (write) new session data.";
					
					die(json_encode($json_out));
				}
			}
			
			// I'm taking a note here.
			$json_out[/*huge*/'success'] = 1;
			
			die(json_encode($json_out));
			
				
			
			
			
			// I'm taking a note here.
			$json_out[/*huge*/'success'] = 1;
			
			die(json_encode($json_out));
			
		
		
		
		
		
		
		
		
		// Here's where the client can request a list of all the 
		// sessions on the server.
		case 'list':
			
			$file_prefix = session_save_path().'/sess_';
			$prefix_n = strlen($file_prefix);
			
			// we read out a list of session files stored on the server
			$sessions = glob($file_prefix.'*');
			
			
			if(isset($_POST['search']) && $_POST['search']){
				$ax_search_value = $_POST['search'];
				$sessions = array_filter($sessions, "ax_search_list");
				
				// lets redo our array indices... wtf php.
				$sessions = array_values($sessions);
			}
			
			
			$n_sessions = count($sessions);
			
			
			// Strip the file prefix from our list of sessions.
			// Foreach is handy but uses more memory with large datasets.
			for($i = 0; $i < $n_sessions; $i++){
				
				$t_sid = $sessions[$i];
				
				$file_size = filesize($t_sid);
				$mod_time = filemtime($t_sid);
				$t_sid = substr($t_sid, $prefix_n);
				
				$sessions[$i] = array('id' => $t_sid, 
				                      'size' => $file_size, 
				                      'mod' => $mod_time);
			}
			
			// we are left with a list of sessions currently stored.
			$json_out['sessions'] = $sessions;
			$json_out['success'] = 1;
			
			die(json_encode($json_out));
		
		
		
		
		
		
		
		case 'user-list':
		
			if(!SPY_ADMIN){
				$json_out['error'] = 'You are not authorized to view this list.';
				die(json_encode($json_out));
			}
				
			$json_out['users'] = list_users();
			$json_out['success'] = 1;
			
			die(json_encode($json_out));
		
		
		
		
		
		
		
		
		case 'user-add':
		
			if(!SPY_ADMIN){
				$json_out['error'] = 'You are not authorized to view this list.';
				die(json_encode($json_out));
			}
			
			
			if(!isset($_POST['user_name']) && !$_POST['user_name']){
				$json_out['error'] = 'user_name is required';
				die(json_encode($json_out));
			}
				
			if(!isset($_POST['user_pass']) && !$_POST['user_pass']){
				$json_out['error'] = 'user_pass is required';
				die(json_encode($json_out));
			}
				
			if(!isset($_POST['user_pass_cf']) && !$_POST['user_pass_cf']){
				$json_out['error'] = 'user_pass_cf is required';
				die(json_encode($json_out));
			}
				
			if(!isset($_POST['user_role']) && !$_POST['user_role']){
				$json_out['error'] = 'user_role is required';
				die(json_encode($json_out));
			}
			
			$user_name    = trim($_POST['user_name']);
			$user_pass    = $_POST['user_pass'];
			$user_pass_cf = $_POST['user_pass_cf'];
			$user_role    = $_POST['user_role'];
			
			
			if(strlen($user_name) < 4 || strlen($user_name) > 32){
				$json_out['error'] = 'User name must be 4-32 characters long';
				die(json_encode($json_out));
			}
			
			if(!preg_match('/^[a-zA-Z0-9_]+$/', $user_name)){
				$json_out['error'] = 'User name contains invalid characters. '.
						'Only letters, numbers, and underscores are allowed.';
				die(json_encode($json_out));
			}
			
			if(strlen($user_pass) < 6 || strlen($user_pass) > 72){
				$json_out['error'] = 'Password must be 8-72 characters long';
				die(json_encode($json_out));
			}
			
			if($user_pass !== $user_pass_cf){
				$json_out['error'] = 'Passwords do not match.';
				die(json_encode($json_out));
			}
			
			if($user_role != 'read' && $user_role != 'write' && $user_role != 'admin'){
				$json_out['error'] = 'User role is not valid.';
				die(json_encode($json_out));
			}
			
			
			if(user_exists($user_name)){
				$json_out['error'] = 'A user with that name already exists.';
				die(json_encode($json_out));
			}
			
			$user_id = add_user($user_name, $user_pass, $user_role);
			
			if(!$user_id){
				$json_out['error'] = 'An unknown error occurred while adding the user.';
				die(json_encode($json_out));
			}
			
			$json_out['user_id'] = $user_id;
			
			$json_out['success'] = 1;
			
			die(json_encode($json_out));
		
		
		
		
		default:
			// malformed request
			$json_out['error'] = 'Unknown action';
			die(json_encode($json_out));
	}
	

	

?>