<?php

	//TODO: Auth

	$json_out = array('success' => 0);

	
	function parse_data($data){
		
		$new_data = array('type' => gettype($data));
		
		switch($new_data['type']){
			
			case 'array':
				
				$keys = array_keys($data);
				
				$c = count($keys);
				
				$new_data['value'] = array();
				
				
				for($i = 0; $i < $c; $i++){
					$new_data['value'][$i] = parse_data($data[$keys[$i]]);
					$new_data['value'][$i]['key'] = $keys[$i];
					}
					
				break;
			
			default:
				$new_data['value'] = $data;
				break;
			}
		
		return $new_data;
		}
	
	
	
	
	if(!isset($_POST['action']) || !$_POST['action']){
		
		$json_out['error'] = 'Action is required';
		
		die(json_encode($json_out));
		}
		
	
	$action = $_POST['action'];
	
	
	switch($action){
		case 'get':
		
			if(!isset($_POST['sid']) || !$_POST['sid']){
				
				$json_out['error'] = 'Session ID is required';
				
				die(json_encode($json_out));
				}
			
			ini_set('session.use_cookies', '0');
			
			$sid = $_POST['sid'];
			
			$json_out['session_id'] = $sid;
			
			session_id($sid);
			
			if(!session_start()){
				$json_out['error'] = 'Could not initialize session';
				die(json_encode($json_out));
				}
			
			
			$json_out['session_new'] = parse_data($_SESSION);
			$json_out['session_new'] = $json_out['session_new']['value'];
			
			
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