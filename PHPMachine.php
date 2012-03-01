<?php

namespace PHPMachine;

require dirname(__FILE__).'/PHPMachine/Loader.php';

\PHPMachine\Loader::autoload();

function http_request($dispatchPath) {
	
	$request = new \PHPMachine\Http\Request();
	$response = new \PHPMachine\Http\Response();

	$response = execute_request($request, $response, $dispatchPath);

	foreach ($response->getHeaders() as $key => $value) {
		header($key . ': ' . $value);
	}
	echo $response->getBody();

}

function execute_request(Request $request, Response $response, $dispatchPath) {
	$dispatchList = require $dispatchPath;

	$result = Dispatcher::dispatch($request->getHost(), $request->getUri(), $dispatchList, $request);

	if ($result[0]===false) {
		$response->setStatusCode(400);
		$body = ErrorHandler::handleError(400, $request, 'Resource was not found');
		$response->setBody($body);
		return $response;
	}
	else {
		return  \PHPMachine\DecisionCore::handleRequest($result[0], $request, $response);
	}

}