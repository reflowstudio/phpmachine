<?php

namespace PHPMachine;

define('PHPMACHINE_DIR', dirname(__FILE__));


class Loader {

	public static function autoload($namespace=__NAMESPACE__, $basepath=PHPMACHINE_DIR, $callback=null) {
		if ($callback === null) {
			spl_autoload_register(function($class) use ($namespace, $basepath){
				if(strpos($class, $namespace . '\\') !== false) {
					require $basepath . '/../' . str_replace('\\', '/', $class) . '.php';
				}
			});
		}
		else {
			spl_autoload_register($callback);
		}
	}

}