<?php


require_once('config.php');




if (get_magic_quotes_gpc()) {
	function undoMagicQuotes($array, $topLevel=true) {
		
		$newArray = array();
		
		foreach($array as $key => $value) {
			if (!$topLevel) {
				$key = stripslashes($key);
				}
			if (is_array($value)) {
				$newArray[$key] = undoMagicQuotes($value, false);
			}else {
				$newArray[$key] = stripslashes($value);
				}
			}
		
		return $newArray;
		}
	
    $_GET = undoMagicQuotes($_GET);
    $_POST = undoMagicQuotes($_POST);
    $_COOKIE = undoMagicQuotes($_COOKIE);
    $_REQUEST = undoMagicQuotes($_REQUEST);
	}





if(!isset($storage)){
	$storage = 'flatfile';
	}

require_once('./include/'.$storage.'.php');

?>