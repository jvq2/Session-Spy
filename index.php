<?php
	define('INSPY', true);
	require_once('./include/common.php');
	require_once('./include/auth.php');


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
<body data-token="<?php echo $_SESSION['sec_token']; ?>">
	<div class="ui-layout-north header">
		<a class="session_spy" href="./">Session Spy</a>
		<span class="me"><b>me:</b> <?php echo session_id();?></span>
	</div>
	
	<div class="ui-layout-west">
	
		<div class="header" style="text-align: center;">
			<div class="sid_search_box">
				<input id="sid_search" type="text" placeholder="Search..." /><button id="sid_search_button"><span>Search</span></button>
			</div>
		</div>
		
		<ul class="ui-layout-content" id="list"></ul>
	</div>
	
	<div class="ui-layout-center">
		<div class="header">
			<div id="cur_sess"></div>
			<span id="loading_data"><img src="images/loading.gif" /></span>
		</div>
		<ul class="ui-layout-content" id="data"></ul>
	</div>
	
	
</body>
</html>