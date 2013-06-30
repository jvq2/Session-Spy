<?php

	//TODO: Auth

	$json_out = array('success' => 0);

	
	//$_POST['s'] = '3ndagjutqp9log1otq3h0vjtg1';
	
	
	if(!isset($_POST['action']) || !$_POST['action']){
		
		$json_out['error'] = 'action is required';
		
		die(json_encode($json_out));
		}
		
	
	$action = $_POST['action'];
	
	
	switch($action){
		case 'get':
		
			if(!isset($_POST['sid']) || !$_POST['sid']){
				
				$json_out['error'] = 'session id is required';
				
				die(json_encode($json_out));
				}
			
			ini_set('session.use_cookies', '0');
			
			$sid = $_POST['sid'];
			
			
			session_id($sid);
			
			session_start();
			
			
			$json_out['session_id'] = $sid;
			$json_out['session'] = $_SESSION;
			$json_out['success'] = 1;
			
			echo json_encode($json_out);
			
			break;
			
		case 'list':
			
			$sessions = glob(session_save_path() .'/sess_*');
			
			$n_sessions = count($sessions);
			
			for($i = 0; $i < $n_sessions; $i++){
				$sessions[$i] = substr($sessions[$i], strlen(session_save_path().'/sess_'));
				}
				
			$json_out['sessions'] = $sessions;
			$json_out['success'] = 1;
			
			echo json_encode($json_out);
			break;
			
		default:
			$json_out['error'] = 'Unknown action';
			die(json_encode($json_out));
		}
	

	

?>