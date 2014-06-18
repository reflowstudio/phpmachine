<?php

namespace PHPMachine;

class ErrorHandler {

	public static $error_handler = null;

	public static function handleError($code, Request $request, $reason) {
		if (static::$error_handler !== null) {
			$handler = static::$error_handler;
			return $handler($code, $request, $reason);
		}
		else {
			return $reason;
		}
	}

}