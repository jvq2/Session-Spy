<?php
	session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link type="text/css" rel="stylesheet" media="screen" href="reset.css">
	<link type="text/css" rel="stylesheet" media="screen" href="layout-default-latest.css">
	<link type="text/css" rel="stylesheet" media="screen" href="style.css">
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery.layout-latest.min.js"></script>
	<script type="text/javascript" src="spy.js"></script>
	
	<title>Session Spy</title>
</head>
<body>
	<div class="ui-layout-north header">
		<span class="session_spy">Session Spy</span>
		<span class="me"><b>me:</b> <?php echo session_id();?></span>
	</div>
	
	<div class="ui-layout-west">
		<div class="header" style="text-align: center;">
			<input id="id_search" type="text" placeholder="Search... (not implemented)" />
		</div>
		<ul class="ui-layout-content" id="list"></ul>
	</div>
	
	<div class="ui-layout-center">
		<div class="header">
			<div id="cur_sess"></div>
			<span id="loading_data"><img src="loading.gif" /></span>
		</div>
		<ul class="ui-layout-content" id="data"></ul>
	</div>
	
	
</body>
</html>
