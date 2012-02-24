<?php

namespace phpmachine;

require dirname(__FILE__).'/src/phpmachine_request.php';
require dirname(__FILE__).'/src/phpmachine_dispatcher.php';

function new_request($host, $port, $method, $scheme, $rawPath, $version, $headers) {

	return phpmachine_request\new_req(array(
			'host' => $host,
			'port' => $port,
			'scheme' => $scheme,  // not sure if we even need this
			'path' => $rawPath,
			'version' => $version,
			'headers' => $headers
		));
}

function http_request() {
	require dirname(__FILE__).'/src/phpmachine_http_helper.php';
	list($host, $port, $method, $scheme, $rawPath, $version, $headers) = phpmachine_http_helper\request_parameters();
	new_request($host, $port, $method, $scheme, $rawPath, $version, $headers);
}
