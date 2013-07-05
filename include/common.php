<?php


require_once('config.php');


if(!isset($storage)){
	$storage = 'flatfile';
	}

require_once('./include/'.$storage.'.php');

?>