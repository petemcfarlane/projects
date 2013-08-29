<?php
require_once __DIR__ . '/../../appframework/3rdparty/SimplePie/autoloader.php';

// to execute without owncloud, we need to create our own classloader
spl_autoload_register(function ($className){
	if (strpos($className, 'OCA\\') === 0) {
	
		$path = strtolower(str_replace('\\', '/', substr($className, 3)) . '.php');
		$relPath = __DIR__ . '/../..' . $path;
	
		if(file_exists($relPath)){
			require_once $relPath;
		}
	}
});
