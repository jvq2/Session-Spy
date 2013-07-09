<?php




function check_login($user, $pass){
	global $pass_salt;
	$data = ff_read();
	
	$pass = md5($pass_salt . $pass);
	
	foreach($data['users'] as $val){
		
		if($val['name'] === $user && $val['pass'] === $pass){
			return array(
						'id' => $val['id'],
						'name' => $val['name'],
						'role' => $val['role']
						);
			}
		
		}
	
	return false;
	}







function user_exists($name){

	$data = ff_read();
	
	foreach($data['users'] as $val){
	
		if($val['name'] === $name){
			return true;
			}
		
		}
	
	return false;
	}









function list_users(){
	
	$data = ff_read();
	
	$users = array();
	
	foreach($data['users'] as $val){
		unset($val['pass']);
		$users[] = $val;
		}
	
	return $users;
	}





function add_user($name, $pass, $role='read'){
	global $pass_salt;
	
	$data = ff_read();
	
	$pass = md5($pass_salt . $pass);
	
	$data['users'][] = array(
				'id'   => $data['meta']['users-nId']++,
				'name' => $name,
				'pass' => $pass,
				'role' => $role
				);
	
	return ff_write($data);
	}







function del_user($id){
	
	$data = ff_read();
	
	$key = NULL;
	
	foreach($data['users'] as $k => $v){
		if($v['id'] == $id){
			$key = $k;
			break;
			}
		}
	
	// user id not found
	if($key === NULL){
		return false;
		}
	
	unset($data['users'][$key]);
	
	return ff_write($data);
	}







function user_role($id, $role=false){
	
	$data = ff_read();
	
	$key = NULL;
	
	foreach($data['users'] as $k => $v){
		if($v['id'] == $id){
			$key = $k;
			break;
			}
		}
	
	// user id not found
	if($key === NULL){
		return false;
		}
	
	if($role === false){
		return $data['users'][$key]['role'];
		}
	
	$data['users'][$key]['role'] = $role;
	
	return ff_write($data);
	}








function user_pass($id, $pass){
	global $pass_salt;
	
	$data = ff_read();
	
	$key = NULL;
	
	foreach($data['users'] as $k => $v){
		if($v['id'] == $id){
			$key = $k;
			break;
			}
		}
	
	// user id not found
	if($key === NULL){
		return false;
		}
	
	
	$data['users'][$key]['pass'] = md5($pass_salt . $pass);
	
	return ff_write($data);
	}







function ff_read(){
	global $flatfile_path;
	
	$f = file_get_contents($flatfile_path);
	return unserialize($f);
	}







function ff_write($data){
	global  $flatfile_path;
	
	$f = serialize($data);
	
	if(!$f){
		return false;
		}
	
	$i = file_put_contents($flatfile_path, $f);
	
	if(!$i){
		return false;
		}
	
	return true;
	}






//TEMPORARY
$flatfile_path = '.spy_storage.dat';

// create the database if one doesnt exist
if(!file_exists($flatfile_path) && $flatfile_path){
	ff_write(
		array(
			'users' => array(),
			'meta' => array(
				'users-nId' => 1
				)
			)
		);
	}




var_dump(user_role(1));
echo '<br />';
var_dump(ff_read());





?>