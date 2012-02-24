<?php
/**
 * phpmachine_dispatcher
 * 
 * @author Cameron Bytheway <cameron@nujii.com>
 */
namespace phpmachine_dispatcher;

define('SEPARATOR', '/');
define('MATCH_ALL', '*');

function dispatch($hostAsString, $pathAsString, $dispatchList, $requestData) {
	$path = explode(SEPARATOR, $pathAsString);

	$extraDepth = ($pathAsString[strlen($pathAsString)-1] == SEPARATOR)?1:0;

	list($host, $port) = _split_host_port($hostAsString);
	return _try_host_binding($dispatchList, 
								array_reverse($host), 
								$port, 
								$path, 
								$extraDepth, 
								$requestData);
}

function _split_host_port($hostAsString) {
	$hostAndPort = explode(':', $hostAsString);

	switch (count($hostAndPort)) {
		case 2:
			return array(_split_host($hostAndPort[0]), (int)$hostAndPort[1]);
			break;

		default:
			return ($hostAndPort[0]=='')?array(array(), 80) : array(_split_host($hostAndPort[0]), 80);
			break;
	}
}

function _try_host_binding($dispatchList, $host, $port, $path, $depth, $requestData) {
	if (!count($dispatchList)) {
		return array(false, array($host, $port), $path);
	}
	$dispatch = $dispatchList[0];

	if (count($dispatch) == 2 && is_array($dispatch[0]) && count($dispatch[0]) == 2) {
		// do nothing
	}
	elseif (count($dispatch) == 2) {
		$dispatch = array(array($dispatch[0], MATCH_ALL), $dispatch[1]);
	}
	else {
		$dispatch = array(array(array(MATCH_ALL), MATCH_ALL), array($dispatch));
	}

	list($hostAndPort, $pathSpec) = $dispatch;

	$portBindings = _bind_port($hostAndPort[0][0], $port, array());

	if ($portBindings === false) {
		return _try_host_binding(array_slice($dispatchList, 1), $host, $port, $path, $depth, $requestData);
	}

	$hostBindingRes = _bind($hostAndPort[0], $host, $portBindings, 0);

	if ($hostBindingRes === false) {
		return _try_host_binding(array_slice($dispatchList, 1), $host, $port, $path, $depth, $requestData);
	}

	$pathBindings = _try_path_binding($pathSpec, $path, $hostBindingRes[0], $port, $hostBindingRes[1], $depth, $requestData);

	if ($pathBindings[0] === false) {
		return _try_host_binding(array_slice($dispatchList, 1), $host, $port, $path, $depth, $requestData);
	}

	array_splice($pathBindings, 2, 0, array($hostBindingRes[1], $port));

	return $pathBindings;
}

function _bind_port($port_match, $port, $bindings) {
	if ($port_match == $port || $port_match == MATCH_ALL) {
		return $bindings;
	}
	elseif (is_string($port_match)) {
		return array_merge(array($port_match => $port), $bindings);
	}
	else {
		return false;
	}
}

function _try_path_binding($pathSpecs, $pathTokens, $hostRemainder, $port, $hostBindings, $extraDepth, $requestData) {
	if (!count($pathSpecs)) {
		return array(false, $pathTokens);
	}
	$pathSpec = $pathSpecs[0];
	if (count($pathSpec) == 3) {
		array_splice($pathSpec, 1, 0, false);
	}

	list($pathSchema, $guard, $namespace, $properties) = $pathSpec;

	$boundPath = _bind($pathSchema, $pathTokens, $hostBindings, $extraDepth);
	if ($boundPath === false) {
		return _try_path_binding(array_slice($pathSpecs, 1), $pathTokens, $hostRemainder, $port, $hostBindings, $extraDepth, $requestData);
	}
	else {
		list($remainder, $pathInfo, $depth) = $boundPath;
		$appRoot = _calculate_app_root($depth+$extraDepth);
		$stringPath = _reconstitute($remainder);
		// TODO build request data
		if (_run_guard($guard, $requestData) == true) {
			return array($namespace, $properties, $remainder, $pathInfo, $appRoot, $stringPath);
		}
		else {
			return _try_path_binding(array_slice($pathSpecs, 1), $pathTokens, $hostRemainder, $port, $hostBindings, $extraDepth, $requestData);
		}
	}
}

function _run_guard($fun, $requestData) {
	if ($fun === false) {
		return true;
	}
	elseif (is_callable($fun)) {
		return $fun($requestData) === true;
	}
}

function _split_host($hostAsString) {
	return explode('.', $hostAsString);
}

function _bind($tokens, $matches, $bindings, $depth) {
	if (!count($tokens) && !count($matches)) {
		return array($matches, $bindings, $depth);
	}
	elseif (count($tokens) == 1 && $tokens[0] == MATCH_ALL) {
		return array($matches, $bindings, $depth+count($matches));
	}
	elseif (!count($matches)) {
		return false;
	}
	elseif (count($tokens) && strpos($tokens[0], '@') !== false) {
		return _bind(array_slice($tokens, 1), 
						array_slice($matches, 1),
						array_merge(array(substr($tokens[0], 1)=>$matches[0]), $bindings), 
						$depth+1);
	}
	elseif (count($tokens) && $tokens[0] == $matches[0]) {
		return _bind(array_slice($tokens, 1), 
						array_slice($matches, 1), 
						$bindings, 
						$depth+1);
	}
	elseif (count($matches) == 1 && $matches[0] == '') {
		return array(array(), $bindings, $depth-1);
	}
	else {
		return false;
	}
}

function _reconstitute($unmatchedTokens) {
	if (!count($unmatchedTokens)) {
		return '';
	}
	else {
		return implode(SEPARATOR, $unmatchedTokens);
	}
}

function _calculate_app_root($number) {
	if ($number==1) {
		return '.';
	}
	elseif ($number>1) {
		return implode(SEPARATOR, array_fill(0, $number, '..'));
	}
}