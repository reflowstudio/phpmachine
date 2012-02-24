<?php

namespace phpmachine_decision_core;

define('RESOURCE_KEY', __NAMESPACE__.'resource');
define('STATE_KEY', __NAMESPACE__.'reqstate');
define('DECISION_KEY', __NAMESPACE__.'decision');
define('CODE_KEY', __NAMESPACE__.'code');


function handle_request($resource, $state) {
	phpmachine_registry\set(RESOURCE_KEY, $resource);
	phpmachine_registry\set(STATE_KEY, $state);
	try {
		return _decision('v3b13');
	}
	catch(Exception $e) {

	}
}

function _wrcall($x) {
	$state = phpmachine_registry\get(STATE_KEY);
	$request = phpmachine_request\new_req($state);
	$requestFun = $request.'\\call';
	list($response, $newState) = $requestFun($x);
	phpmachine_registry\set(STATE_KEY, $newState);
	return $response;
}

function _resource_call($function) {
	$resource = phpmachine_registry\get(RESOURCE_KEY);
	$resourceFun = $resource.'\\do';
	list($reply, $newResource, $newState) = $resourceFun($function, phpmachine_registry\all());
	phpmachine_registry\set(RESOURCE_KEY, $newResource);
	phpmachine_registry\set(STATE_KEY, $newState);
	return $reply;
}

function _get_header_val($headers) {
	return _wrcall(array('get_req_header', $headers));
}

function _method() {
	return _wrcall(array('method'));
}

function _decision($id) {
	phpmachine_registry\set(DECISION_KEY, $id);
	// log the decision
	$fun = '_decision_'.$id;
	return $fun();
}

function _respond($code, $headers = NULL) {
	if ($headers !== NULL) {
		_wrcall(array('set_resp_headers', $headers));
	}

	$resource = phpmachine_registry\get(RESOURCE_KEY);
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

	phpmachine_registry\set(CODE_KEY, $code);
	_wrcall(array('set_response_code', $code));
	_resource_call('finish_response');
	$rnamespace = _wrcall(array('get_metadata', 'resource_module'));
	$notes = _wrcall(array('notes'));
	$logData = _wrcall(array('log_data'));
	$logData['resource_namespace'] = $rnamespace;
	$logData['end_time'] = $endTime;
	$logData['notes'] = $notes;

	// do log
	
	$resourceFun = $resource.'\\stop';
	$resourceFun();
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
	$resource = phpmachine_registry\get(RESOURCE_KEY);
	$resourceFun = $resource.'\\log_d';
	return $resourceFun($id);
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
		return _decision('v3b9');
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
	if($isAuthorized === true) {
		return _decision('v3b7');
	}
	elseif (is_array($isAuthorized)) {
		if ($isAuthorized[0] == 'error') {
			return _error_response($isAuthorized[1]);
		}
		else {
			return _respond($isAuthorized[1]);
		}
	}
	else {
		_wrcall(array('set_resp_header', 'WWW-Authenticate', $isAuthorized));
		return _respond(401);
	}
}

// Forbidden ?
function _decision_v3b7() {
	return _decision_test(_resource_call('forbidden'), true, 403, 'v3b6');
}

// Okay Content-* Headers?
function _decision_v3b6() {
	return _decision_test(_resource_call('valid_content_headers'), true, 'v3b5', 501);
}

// Known Content-Type?
function _decision_v3b5() {
	return _decision_test(_resource_call('known_content_type'), true, 'v3b4', 415);
}

// Req Entity Too Large?
function _decision_v3b4() {
	return _decision_test(_resource_call('valid_entity_length'), true, 'v3b3', 413);
}

// OPTIONS?
function _decision_v3b3() {
	$method = _method();
	if ($method == 'OPTIONS') {
		$headers = _resource_call('options');
		return _respond(200, $headers);
	}
	else {
		return _decision('v3c3');
	}
}

// Accept exists?
function _decision_v3c3() {
	$accept = _get_header_val('accept');
	if ($accept === null) {
		$providedTypes = _resource_call('content_types_provided');
		_wrcall(array('set_metadata', 'content-type', $providedTypes[0]));
		return _decision('v3d4');
	}
	else {
		return _decision('v3c4');
	}
}

// Acceptable media type available?
function _decision_v3c4() {
	$providedTypes = _resource_call('content_types_provided');
	$accept = _get_header_val('accept');
	$type = phpmachine_util\choose_media_type($providedTypes, $accept);

	if ($type === null) {
		return _respond(406);
	}
	else {
		_wrcall(array('set_metadata', 'content-type', $type));
		return _decision('v3d4');
	}
}

// Accept-Language exists?
function _decision_v3d4() {
	return _decision_test(get_header_val('accept-language'), null, 'v3e5', 'v3d5');
}

// Acceptable Language available?
function _decision_v3d5() {
	return _decision_test(_resource_call('language_available'), true, 'v3e5', 406);
}

// Accept-Charset exists?
function _decision_v3e5() {
	$charset = _get_header_val('accept-charset');

	if($charset === null) {
		return _decision_test(_choose_charset('*'), 'none', 406, 'v3f6');
	}
	else {
		return _decision('v3e6');
	}
}

// Accept-Encoding exists?
function _decision_v3f6() {
	$contentType = _wrcall(array('get_metadata', 'content-type'));
	$charset = _wrcall(array('get_metadata', 'chosen-charset'));
	if ($charset === null) {
		$charset = '';
	}
	else {
		$charset = '; charset=' . $charset;
	}

	_wrcall(array('set_resp_headers', 'Content-Type', $contentType . $charset));

	$encoding = _get_header_val('accept-encoding');
	if ($encoding === null) {
		return _decision_test(_choose_encoding('identity;q=1.0,*;q=0.5'), 'none', 406, 'v3g7');
	}
	else {
		return _decision('v3f7');
	}
	
}

// Acceptable encoding available?
function _decision_v3f7() {
	return _decision_test(_choose_encoding(_get_header_val('accept-encoding')), 'none', 406, 'v3g7');
}

// Resource exists?
function _decision_v3g7() {
	$variances = _variances();
	if (is_array($variances) && count($variances)) {
		_wrcall(array('set_resp_header', 'Vary', implode(', ', $variances)));
	}
	return _decision_test(_resource_call('resource_exists'), true, 'v3g8', 'v3h7');
}

// If-Match exists?
function _decision_v3g8() {
	return _decision_test(_get_header_val('if-match'), null, 'v3h10', 'v3g11');
}

// If-Match: * exists?
function _decision_v3g9() {
	return _decision_test(_get_header_val('if-match'), '*', 'v3h10', 'v3g11');
}

// ETag in If-Match
function _decision_v3g11() {
	$etags = phpmachine_util\split_quoted_strings(_get_header_val('if-match'));
	return _decision_test(_resource_call('generate_etag'), 
							function($etag) use ($etags) { return in_array($etag, $etags); }, 
							'v3h10', 
							412);
}

// If-Match exists?
function _decision_v3h7() {
	return _decision_test(_get_header_val('if-match'), null, 'v3i7', 412);
}

// If-unmodified-since exists?
function _decision_v3h10() {
	return _decision_test(_get_header_val('if-unmodified-since'), null, 'v3i12', 'v3h11');
}

// I-UM-S is valid date?
function _decision_v3h11() {
	$IUMSDate = _get_header_val("if-unmodified-since");
	return _decision_test(phpmachine_util\convert_request_date($IUMSDate), 'bad_date', 'v3i12', 'v3h12');
}

// Last-Modified > I-UM-S?
function _decision_v3h12() {
	$requestDate = _get_header_val("if-unmodified-since");
	$reqPhpDate = phpmachine_util\convert_request_date($requestDate);
	$resPhpDate = _resource_call('last_modified');
	return _decision_test($resPhpDate > $reqPhpDate, true, 412, 'v3i12');
}

// Moved permanently? (apply PUT to different URI)
function _decision_v3i4() {
	$moved = _resource_call('moved_permanently');
	if ($moved === false) {
		return _decision('v3p3');
	}
	elseif (is_array($moved)) {
		if ($moved[0] === true) {
			_wrcall(array('set_resp_header', 'Location', $moved[1]));
			return _respond(301);
		}
		elseif ($moved[0] == 'error') {
			return _error_response($moved[1]);
		}
		else {
			return _respond($moved[1]);
		}
	}
}

// PUT?
function _decision_v3i7() {
	return _decision_test(_method(), 'PUT', 'v3i4', 'v3k7');
}

// If-None-Match exists?
function _decision_v3i12() {
	return _decision_test(_get_header_val('if-none-match'), null, 'v3l13', 'v3i13');
}

// If-None-Match: * exists?
function _decision_v3i13() {
	return _decision_test(_get_header_val('if-none-match'), '*', 'v3j18', 'v3k13');
}

// GET or HEAD?
function _decision_v3j18() {
	return _decision_test(in_array(_method(), array('GET', 'HEAD')), true, 304, 412);
}

// Moved permanently?
function _decision_v3k5() {
	$moved = _resource_call('moved_permanently');
	if ($moved === false) {
		return _decision('v3l5');
	}
	elseif (is_array($moved)) {
		if ($moved[0] === true) {
			_wrcall(array('set_resp_header', 'Location', $moved[1]));
			return _respond(301);
		}
		elseif ($moved[0] == 'error') {
			return _error_response($moved[1]);
		}
		else {
			return _respond($moved[1]);
		}
	}
}

// Previously existed?
function _decision_v3k7() {
	return _decision_test(_resource_call('previously_existed'), true, 'v3k5', 'v3l7');
}

// Etag in if-none-match?
function _decision_v3k13() {
	$etags = phpmachine_util\split_quoted_strings(_get_header_val('if-none-match'));
	return _decision_test(_resource_call('generate_etag'), 
							function($etag) use ($etags) { return in_array($etag, $etags); }, 
							'v3j18', 
							'v3l13');
}

// Moved temporarily?
function _decision_v3l5() {
	$moved = _resource_call('moved_temporarily');
	if ($moved === false) {
		return _decision('v3m5');
	}
	elseif (is_array($moved)) {
		if ($moved[0] === true) {
			_wrcall(array('set_resp_header', 'Location', $moved[1]));
			return _respond(307);
		}
		elseif ($moved[0] == 'error') {
			return _error_response($moved[1]);
		}
		else {
			return _respond($moved[1]);
		}
	}
}

// POST?
function _decision_v3l7() {
	return _decision_test(_method(), 'POST', 'v3m7', 404);
}

// IMS exists?
function _decision_v3l13() {
	return _decision_test(_get_header_val('if-modified-since'), null, 'v3m16', 'v3l14');
}

// IMS is valid date?
function _decision_v3l14() {
	$IMSDate = _get_header_val("if-unmodified-since");
	return _decision_test(phpmachine_util\convert_request_date($IMSDate), 'bad_date', 'v3m16', 'v3l15');
}

// IMS > Now?
function _decision_v3l15() {
	$nowDateTime = gmdate("Y-m-d\TH:i:s\Z");
	$requestDate = _get_header_val("if-modified-since");
	$reqPhpDate = phpmachine_util\convert_request_date($requestDate);
	return _decision_test($reqPhpDate > $nowDateTime, true, 'v3m16', 'v3l17');
}

// Last-Modified > IMS?
function _decision_v3l17() {
	$requestDate = _get_header_val("if-modified-since");
	$reqPhpDate = phpmachine_util\convert_request_date($requestDate);
	$resPhpDate = _resource_call('last_modified');
	return _decision_test($resPhpDate === null || $resPhpDate > $reqPhpDate, 
							true, 'v3m16', 304);
}

// POST?
function _decision_v3m5() {
	return _decision_test(_method(), 'POST', 'v3n5', 410);
}

// Server allows POST to missing resource?
function _decision_v3m7() {
	return _decision_test(_resource_call('allow_missing_post'), true, 'v3n11', 404);
}

// DELETE?
function _decision_v3m16() {
	return _decision_test(_method(), 'DELETE', 'v3m20', 'v3n16');
}

// DELETE enacted immediately?
// Also where DELETE is forced.
function _decision_v3m20() {
	return _decision_test(_resource_call('delete_resource'), true, 'v3m20b', 500);
}
function _decision_v3m20b() {
	return _decision_test(_resource_call('delete_completed'), true, 'v3o20', 202);
}

// Server allows POST to missing resource?
function _decision_v3n5() {
	return _decision_test(_resource_call('allow_missing_post'), true, 'v3n11', 410);
}

// TODO
// Redirect?
function _decision_v3n11() {
	$postIsCreate = _resource_call('post_is_create');
	if (postIsCreate === true) {
		
	}
}

// POST?
function _decision_v3n16() {
	return _decision_test(_method(), 'POST', 'v3n11', 'v3o16');
}

// Conflict?
function _decision_v3o14() {
	$isConflict = _resource_call('is_conflict');
	if ($isConflict === true) {
		return _respond(409);
	}
	else {
		$response = _accept_helper();
		if ($response[0] == 'respond') {
			return _respond($response[1]);
		}
		elseif ($response[0] == 'halt') {
			return _respond($response[1]);
		}
		elseif ($response[0] == 'error') {
			return _error_response($response);
		}
		else {
			return _decision('v3p11');
		}
	}
}

// PUT?
function _decision_v3o16() {
	return _decision_test(_method(), 'PUT', 'v3o14', 'v3o18');
}

// TODO
// Multiple representations?
// (also where body generation for GET and HEAD is done)
function _decision_v3o18() {
	$method = _method();
	$buildBody = ($method == 'GET' || $method == 'HEAD');


}
function _decision_v3o18b() {
	return _decision_test(_resource_call('multiple_choices'), true, 300, 200);
}

// Response includes an entity?
function _decision_v3o20() {
	return _decision_test(_wrcall(array('has_resp_body')), true, 'v3o18', 204);
}

// Conflict?
function _decision_v3p3() {
	$isConflict = _resource_call('is_conflict');
	if ($isConflict == true) {
		return _respond(409);
	}
	else {
		$response = _accept_helper();
		if ($response[0] == 'respond') {
			return _respond($response[1]);
		}
		elseif ($response[0] == 'halt') {
			return _respond($response[1]);
		}
		elseif ($response[0] == 'error') {
			return _error_response($response);
		}
		else {
			return _decision('v3p11');
		}
	}
}

// New resource?
// (at this point boils down to "has location header")
function _decision_v3p11() {
	$location = _wrcall(array('get_resp_header', 'Location'));
	if ($location === null) {
		return _decision('v3o20');
	}
	else {
		return _respond(201);
	}
}

