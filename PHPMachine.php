<?php

namespace PHPMachine;

require dirname(__FILE__).'/PHPMachine/Loader.php';

\PHPMachine\Loader::autoload();

function http_request() {
	
	$request = new \PHPMachine\Http\Request();

	$dispatchList = array(array(array('/'),'phpmachine_resource',''));

	$result = \phpmachine_dispatcher\dispatch($request->getHost(), $request->getPath(), $dispatchList, $request);

	if ($result[0]=='no_dispatch_match') {
		// todo more graceful error handling
		echo 'not found';
	}
	else {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestResource', $request, new \PHPMachine\Http\Response());
		foreach ($response->getHeaders as $key => $value) {
			header($key . ': ' . $value);
		}
		echo $response->getBody();
	}
}
