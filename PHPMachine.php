<?php

namespace PHPMachine;

require dirname(__FILE__).'/src/Loader.php';

\PHPMachine\Loader::autoload();

function http_request($dispatchPath) {
	$request = new \PHPMachine\Request();
	$response = new \PHPMachine\Response();
	$response = execute_request($request, $response, $dispatchPath);
	$response->serve();
}

function execute_request(Request $request, Response $response, $dispatchPath) {
	$dispatchList = require $dispatchPath;

	$result = Dispatcher::dispatch($dispatchList, $request);

	if ($result[0]===false) {
		$response->set_status_code(400);
		$body = ErrorHandler::handleError(400, $request, 'Resource was not found');
		$response->write($body);
		return $response;
	}
	else {
		return  \PHPMachine\DecisionCore::handleRequest($result[0], $request, $response);
	}

}
