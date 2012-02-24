<?php

namespace phpmachine;

require dirname(__FILE__).'/src/prq.php';
require dirname(__FILE__).'/src/phpmachine_request.php';
require dirname(__FILE__).'/src/phpmachine_dispatcher.php';
require dirname(__FILE__).'/src/phpmachine_registry.php';

function new_request($host, $port, $method, $scheme, $rawPath, $version, $headers) {

	$request = new \phpmachine\prq($method, $scheme, $version, $rawPath, $headers);

	return \phpmachine_request\new_req($request);
}

function http_request() {
	require dirname(__FILE__).'/src/phpmachine_http_helper.php';
	list($host, $port, $method, $scheme, $rawPath, $version, $headers) = \phpmachine_http_helper\request_parameters();

	$request = new_request($host, $port, $method, $scheme, $rawPath, $version, $headers);

	$dispatchList = array(array(array('/'),'phpmachine_resource',''));

	$result = \phpmachine_dispatcher\dispatch($request->get_host(), $request->get_path(), $dispatchList, $request);

	if ($result[0]=='no_dispatch_match') {
		// todo more graceful error handling
		echo 'not found';
	}
	else {
		list($namespace, $options, $hostTokens, $port, $pathTokens, $bindings, $appRoot, $stringPath) = $result;
		$request->load_dispatch_data($bindings, $hostTokens, $port, $pathTokens, $appRoot, $stringPath);
		try {
			require dirname(__FILE__).'/src/phpmachine_resource.php';
			require dirname(__FILE__).'/src/phpmachine_decision_core.php';
			$result = \phpmachine_decision_core\handle_request('phpmachine_resource', $request);
			var_dump($result);
		}
		catch(Exception $e) {
			echo 'there was an error';
		}
		
	}
}
