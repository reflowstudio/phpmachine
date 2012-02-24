<?php

namespace phpmachine_decision_core;

define('RESOURCE_KEY', __NAMESPACE__.'resource');
define('STATE_KEY', __NAMESPACE__.'reqstate');
define('DECISION_KEY', __NAMESPACE__.'decision');
define('CODE_KEY', __NAMESPACE__.'code');


function handle_request($resource, $state) {
	phpmachine_registry/set(RESOURCE_KEY, $resource);
	phpmachine_registry/set(STATE_KEY, $state);
	try {
		_decision('v3b13');
	}
	catch($e) {

	}
}

function _wrcall($x) {
	$state = phpmachine_registry/get(STATE_KEY);
	$request = phpmachine_request/new($state);
	list($response, $newState) = $request:call($x);
	phpmachine_registry/set(STATE_KEY, $newState);
	return $response;
}

function _resource_call($function) {
	$resource = phpmachine_registry/get(RESOURCE_KEY);
	list($reply, $newResource, $newState) = $resource:do($function, phpmachine_registry/all());
	phpmachine_registry/set(RESOURCE_KEY, $newResource);
	phpmachine_registry/set(STATE_KEY, $newState);
	return $reply;
}

function _get_header_val($headers) {
	return _wrcall(array('get_req_header', $headers));
}

function _method() {
	return _wrcall(array('method'));
}

function _decision($id) {
	phpmachine_registry/set(DECISION_KEY, $id);
	// log the decision
	$fun = '_decision_'.$id;
	return $fun();
}

function _respond($code, $headers = NULL) {
	if ($headers !== NULL) {
		_wrcall(array('set_resp_headers', $headers));
	}

	$resource = phpmachine_registry/get(RESOURCE_KEY);
	$endTime = time();

	switch ($code) {
		case 404:
			// log stuff
			break;
		case 304:
			// do expiration stuff
			break;
		
		default:
			break;
	}

	phpmachine_registry/set(CODE_KEY, $code);
	_wrcall(array('set_response_code', $code));
	_resource_call('finish_response');
	$rnamespace = _wrcall(array('get_metadata', 'resource_module'));
	$notes = _wrcall(array('notes'));
	$logData = _wrcall(array('log_data'));
	$logData['resource_namespace'] = $rnamespace;
	$logData['end_time'] = $endTime;
	$logData['notes'] = $notes;

	// do log
	
	$resource\stop();
}

function _error_response($reason, $code = 500) {
	// todo call error handler and render error in desired format

	//phpmachine_registry\set(STATE_KEY, $state);
	//_wrcall({set_resp_body, $response});
	_respond($code);
}

function _decision_test($test, $testVal, $trueFlow, $falseFlow) {
	if (is_array($test)) {
		if ($test[0] == 'error') {
			if (count($test)==2) {
				return _error_response($test[1]);
			}
			elseif (count($test)==3) {
				return _error_response(array($test[1],$test[2]));
			}
		}
		elseif ($test[0] == 'halt') {
			return _respond($test[0]);
		}
		elseif ($test[0]==$testVal) {
			return _decision_flow($trueFlow, $test);
		}
		else {
			return _decision_flow($falseFlow, $test);
		}
	}
}

function _decision_flow($x, $testResult) {
	if (is_integer($x)) {
		if ($x >= 500 ) {
			return _error_response($x, $testResult);
		}
		else {
			return _respond($x);
		}
	}
	else {
		return _decision($x);
	}
}

function do_log($logData) {
	// log data
}

function _log_decision($id) {
	$resource = phpmachine_registry/get(RESOURCE_KEY);
	return $resource\log_d($id);
}

// Service Available
function _decision_v3b13() {
	return _decision_test(_resource_call('ping'), 'pong', 'v3b13b', 503);
}
function _decision_v3b13b() {
	return _decision_test(_resource_call('service_available'), true, 'v3b12', 503);
}

// Known method
function _decision_v3b12() {
	return _decision_test(in_array(_method(), _resource_call('known_methods')) , true, 'v3b11', 501);
}

// URI too long?
function _decision_v3b11() {
	return _decision_test(_resource_call('uri_too_long') , true, 414, 'v3b10');
}

// Method allowed?
function _decision_v3b10() {
	$methods = _resource_call('allowed_methods');
	if (in_array(_method(), $methods)) {
		return _decision_v3b9();
	}
	else {
		_wrcall(array('set_resp_headers', array('Allow'=>implode(', ', $methods))));
		return _respond(405);
	}
}

// Malformed?
function _decision_v3b9() {
	return _decision_test(_resource_call('malformed_request') , true, 400, 'v3b8');
}

// Authorized?
function _decision_v3b8() {
	$isAuthorized = _resource_call('is_authorized');

	return _decision_test(_resource_call('malformed_request') , true, 400, 'v3b8');
}

