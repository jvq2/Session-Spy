<?php
	define('INSPY', true);
	require_once('./include/common.php');
	require_once('./include/auth.php');

	
	$default_sess = '';
	if(isset($_GET['session_id']) && $_GET['session_id']){
		$default_sess = ' data-sid="'.htmlentities($_GET['session_id']).'"';
		}
	


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link type="text/css" rel="stylesheet" media="screen" href="css/reset.css">
	<link type="text/css" rel="stylesheet" media="screen" href="css/layout-default-latest.css">
	<link type="text/css" rel="stylesheet" media="screen" href="css/style.css">
	<script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.10.3.min.js"></script>
	<script type="text/javascript" src="js/jquery.layout-latest.min.js"></script>
	
	<script type="text/javascript" src="js/spy.js"></script>
	
	<title>Session Spy</title>
</head>
<body data-token="<?php echo $_SESSION['sec_token']; ?>" data-role="<?php echo $_SESSION['user']['role']; ?>" data-start-min="<?php echo $default_sess?'true':'false';?>">
	<div class="ui-layout-north header">
		<a class="session_spy" href="./">Session Spy</a>
		<button id="logout" title="Logout">Logout</button>
		<?php if(SPY_ADMIN){ ?><button id="show_users" title="Open Users Panel">Users</button><?php } ?>
		<span class="me"><b>me:</b> <?php echo session_id();?></span>
	</div>
	
	<div class="ui-layout-west">
	
		<div class="header" style="text-align: center;">
			<div class="sid_search_box">
				<input id="sid_search" type="text" placeholder="Search..." /><button id="sid_search_button" title="Search in Session ID's"><span>Search</span></button>
			</div>
		</div>
		
		<ul class="ui-layout-content" id="list"></ul>
	</div>
	
	<div class="ui-layout-center">
		<div class="header">
			<button title="Refresh Session Data" class="green" id="refresh_data">
				<span>Refresh</span>
			</button>
			
			<button class="red" id="delete_session" title="Delete Session">
				<span>Delete Session</span>
			</button>
			
			<div id="cur_sess"></div>
			
			<span id="loading_data"><img src="images/loading.gif" /></span>
			
				
			<span id="data_view_toolbar">
				<button id="toggle_panels" title="Toggle Panels">
					<span>Toggle all panels</span>
				</button>
				<button id="data_new_window" title="Open in New Window">
					<span>Open in new window</span>
				</button>
			</span>
			
		</div>
		
		<ul class="ui-layout-content" id="data"<?php echo $default_sess;?>></ul>
	</div>
	
	<?php if(SPY_ADMIN){ ?>
	<div class="ui-layout-east">
		<div class="header">
			<span>Users</span>
			<button id="add_user"><span>Add</span></button>
		</div>
		<ul class="ui-layout-content" id="users"></ul>
	</div>
	<?php } ?>
	
	<div id="add_user_dialog">
		<form>
			<input type="text" id="add_user_name" /><br />
			<input type="password" id="add_user_pass" /><br />
			<input type="password" id="add_user_pass_c" /><br />
			<select id="add_user_role">
				<option value="read">Readonly</option>
				<option value="write">Edit</option>
				<option value="admin">Admin</option>
			</select>
		</form>
	</div>
	
	
</body>
</html>