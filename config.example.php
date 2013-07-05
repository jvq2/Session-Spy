<?php



//// Setting storage method. Both 'mysql-db' and 'flatfile' are 
//// included by default.
// Default: 'flatfile'
$storage = 'mysql-db';

//// Path to mysql server.
// Default: 'localhost'
$mysql_host		= 'localhost';

//// User credentials
$mysql_user		= '';
$mysql_pass		= '';

//// Database name.
// Default: 'session_spy'
$mysql_db		= 'session_spy';

//// Table prefix. 
// Default: 'sspy_'
$mysql_prefix	= 'sspy_';



$flatfile_path = '.spy_storage.dat';

//// Password salt. Change this. Add/remove some characters.
//// Just change it. It can be any length -- the longer the better.

$pass_salt = '3a6s7sgd36p2ZWfu46Y1O7lqnTY5MWY3OTE1MTdiMTJ';

?>