<?php
	define('INSPY', true);
	define('SPY_JSON', true);
	require_once('auth.php');
	
	
	// close the auth session and clear its contents
	session_write_close();
	session_unset();

	$json_out = array('success' => 0);

	// only allow request that have past THE TESTS!!!!
	if(!SPY_SEC){
		$json_out['error'] = 'Security token not found.';
		die(json_encode($json_out));
		}
	
	
	function parse_data($data){
		
		$new_data = array('type' => gettype($data));
		
		switch($new_data['type']){
			
			case 'object':
				
				$name = get_class($data);
				
				$data_a = (array)$data;
				
				
				$keys = @array_keys($data_a);
				
				$c = count($keys);
				
				$new_data['value'] = array();
				$new_data['class'] = htmlspecialchars($name);
				
				for($i = 0; $i < $c; $i++){
					$k = $keys[$i];
					$priv = 0;
					$prot = 0;
					
					$k = str_replace("\0". $name ."\0", '', $k, $priv);
					
					$k = str_replace("\0*\0", '', $k, $prot);
					
					$new_data['value'][$i] = parse_data($data_a[$k]);
					$new_data['value'][$i]['key'] = htmlspecialchars($k);
					$new_data['value'][$i]['flag'] = $priv?'private' : $prot?'protected' : 'public';
					
					//echo '"'. str_replace("\0".$name."\0",'PRIVATE-',$keys[$i]) .'": "'. $b[$keys[$i]] .'"<br />';
					
					}
				break;
			case 'array':
				
				$keys = array_keys($data);
				
				$c = count($keys);
				
				$new_data['value'] = array();
				
				
				for($i = 0; $i < $c; $i++){
					$new_data['value'][$i] = parse_data($data[$keys[$i]]);
					$new_data['value'][$i]['key'] = htmlspecialchars($keys[$i]);
					}
					
				break;
			case 'boolean':
				$new_data['value'] = $data;
				break;
			default:// other scalar types
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
		case 'get':
		
			if(!isset($_POST['sid']) || !$_POST['sid']){
				
				$json_out['error'] = 'Session ID is required';
				
				die(json_encode($json_out));
				}
			
			// dont send this sessions cookie to the client
			ini_set('session.use_cookies', '0');
			
			$sid = $_POST['sid'];
			
			$json_out['session_id'] = $sid;
			
			// set phasers to stun
			session_id($sid);
			
			if(!session_start()){
				$json_out['error'] = 'Could not initialize session';
				die(json_encode($json_out));
				}
			
			
			$json_out['session'] = parse_data($_SESSION);
			// The process leaves a one item root node. Lets remove it.
			$json_out['session'] = $json_out['session']['value'];
			
			// I'm taking a note here.
			$json_out[/*huge*/'success'] = 1;
			
			echo json_encode($json_out);
			
			break;
		
		
		// Heres where the client can request a list of all the 
		// sessions on the server.
		case 'list':
			
			// we read out a list of session files stored on the server
			$sessions = glob(session_save_path() .'/sess_*');
			
			$n_sessions = count($sessions);
			
			// Strip the file prefix from our list of sessions.
			// Foreach is handy but more intensive.
			for($i = 0; $i < $n_sessions; $i++){
				$sessions[$i] = substr($sessions[$i], strlen(session_save_path().'/sess_'));
				}
			
			// we are left with a list of sessions currently stored.
			$json_out['sessions'] = $sessions;
			$json_out['success'] = 1;
			
			echo json_encode($json_out);
			break;
			
		default:
			// malformed request
			$json_out['error'] = 'Unknown action';
			die(json_encode($json_out));
		}
	

	

?>