<?php
	define('INSPY', true);
	define('SPY_JSON', true);
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





	function parse_data($data){
		
		$new_data = array('type' => gettype($data));
		
		switch($new_data['type']){
			
			case 'object':
				
				$name = get_class($data);
				
				$data_a = (array)$data;
				
				
				
				$new_data['value'] = array();
				$new_data['class'] = htmlspecialchars($name);
				
				$i = 0;
				
				foreach($data as $key => $val){
					$priv = 0;
					$prot = 0;
					
					$k = str_replace("\0". $name ."\0", '', $key, $priv);
					
					$k = str_replace("\0*\0", '', $k, $prot);
					
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
			
			
			if(!session_decode($s)){
				$_SESSION = $old_session;
				
				$json_out['error'] = 'Could not decode session '.var_export($_SESSION,true);
				die(json_encode($json_out));
				}
			
			// save read session data
			$data = $_SESSION;
			
			// restore previous session so that we dont lose our login
			$_SESSION = $old_session;
			
			
			// magic
			$json_out['session'] = parse_data($data);
			
			// The process leaves a one item root node. Lets remove it.
			$json_out['session'] = $json_out['session']['value'];
			
			// I'm taking a note here.
			$json_out[/*huge*/'success'] = 1;
			
			die(json_encode($json_out));
			
		
		
		
		
		
		
		// Heres where the client can request a list of all the 
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