<?php

set_include_path(get_include_path().PATH_SEPARATOR.'autoload/');



function class_creator($class) {
	eval("class ". $class ." {
		public function __get(\$name){
			//error_log('get called on '.\$name.': '.\$this->\$name);
			//
			return \$this->\$name;
		}
		public function __set(\$name, \$val){
			//error_log('set called on '.\$name.': '.\$this->\$name);
			\$this->\$name = \$val;
		}
		public function __isset(\$name){
			//error_log('isset called on '.\$name.': '.\$this->\$name);
			//return isset(\$this->\$name);
			return array_key_exists(\$name, (array)\$this);
		}
		public function __unset(\$name){
			//error_log('unset called on '.\$name.': '.\$this->\$name);
			//return \$this->\$name = NULL;
			unset(\$this->\$name);
		}
	};");
}

spl_autoload_extensions(".inc,.php,.lib,.lib.php,.class.php,.function.php");

spl_autoload_register();

///
// place any other auto-loaders here




// keep this line last
spl_autoload_register('class_creator');
