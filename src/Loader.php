<?php

namespace PHPMachine;

define('PHPMACHINE_DIR', dirname(__FILE__));

class Loader {

	public static function autoload($namespace=__NAMESPACE__, $basepath=PHPMACHINE_DIR, $includeFirstNamespace=false) {
		spl_autoload_register(function($class) use ($namespace, $basepath, $includeFirstNamespace){
			if(strpos($class, $namespace . '\\') !== false) {
				$classPath = str_replace('\\', '/', $class);
				if (!$includeFirstNamespace) {
					$index = strpos($classPath, '/', 1);
					$classPath = substr($classPath, $index);
				}
				require $basepath . $classPath . '.php';
			}
		});
	}

	public static function customAutoload($callback=null) {
		spl_autoload_register($callback);
	}

}
