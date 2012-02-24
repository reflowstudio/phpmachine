<?php

namespace phpmachine_http_helper;

function request_parameters() {
	return array($_SERVER['SERVER_NAME'], 
		$_SERVER['SERVER_PORT'], 
		$_SERVER['REQUEST_METHOD'], 
		isset($_SERVER['HTTPS'])?'https':'http',
		$_SERVER['REQUEST_URI'],
		$_SERVER['SERVER_PROTOCOL'],
		_getallheaders());
}

function _getallheaders() {
	foreach ($_SERVER as $name => $value) {
		if (substr($name, 0, 5) == 'HTTP_') {
			$name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
			$headers[$name] = $value;
		} else if ($name == "CONTENT_TYPE") {
			$headers["Content-Type"] = $value;
		} else if ($name == "CONTENT_LENGTH") {
			$headers["Content-Length"] = $value;
		}
	}
	return $headers;
}

