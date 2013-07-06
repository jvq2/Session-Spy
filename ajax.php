<?php
	define('INSPY', true);
	define('SPY_JSON', true);
	require_once('./include/common.php');
	require_once('./include/auth.php');
	
	
	
	// Close the current session and clear its contents
	// WARNING: DO NOT REMOVE - removal of the following lines will
	//          cause unwanted alterations or possibly corruptions
	//          to any viewed sessions.
	session_write_close();
	session_unset();
	//////
	
	
	

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
				
				
				$keys = array_keys($data_a);
				
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
			// Foreach is handy but more expensive to call for large datasets.
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
			
			echo json_encode($json_out);
			break;
		
		
		
		default:
			// malformed request
			$json_out['error'] = 'Unknown action';
			die(json_encode($json_out));
		}
	

	

?>