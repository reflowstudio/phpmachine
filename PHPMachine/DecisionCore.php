<?php
/**
 * PHPMachine DecisionCore
 * 
 * @author Cameron Bytheway <cameron@nujii.com>
 */
namespace PHPMachine;

class DecisionCore {

	/**
	 * Walks a request though the HTTP Flow Chart
	 * @param  string   $resource Target Resource
	 * @param  Request  $request
	 * @param  Response $response
	 * @return Response
	 */
	public static function handleRequest($resource, Request $request, Response $response) {
		try {
			// Temporary
			$response->setMetadataItem('start-time', microtime(true));

			$context = $resource::init($request);

			$state = new DecisionCoreState($resource, $request, $response, $context);

			// I think the loop will be more effecient than recursion in PHP
			$decision = 'v3b13';
			while($decision != 'stop') {
				$decision = static::decision($decision, $state);
			}
			return $response;
		}
		catch(Exception $e) {
			static::errorResponse($e->getMessage().'\n'.$e->getTraceAsString(), $state);
		}
	}

	protected static function callResource($method, DecisionCoreState $state) {
		$resource = $state->resource;
		try {
			if (!is_callable(array($resource, $method))) {
				throw new \Exception("Resource is not fully implemented! Could not call $resource::$method", 1);
			}
			return $resource::$method($state->request, $state->context);
		}
		catch(\Exception $e) {
			return $e;
		}
		
	}

	protected static function decision($decision, DecisionCoreState $state) {
		Logger::log(Logger::TYPE_DECISION, $decision, $state->request, $state->response);
		$fun = 'decision_' . $decision;
		return static::$fun($state);
	}

	protected static function respond($code, DecisionCoreState $state) {

		$request = $state->request;
		$response = $state->response;

		if ($code == 404) {
			$resonse = ErrorHandler::handleError($code, $request, 'Resource was not found');
		}
		elseif ($code == 304) {
			// TODO
			// remove Content-Type header
			// generate ETag
			// Get Expires
		}

		$response->setStatusCode($code);
		static::callResource('finishRequest', $state);

		$response->setMetadataItem('end-time', microtime(true));

		return 'stop';
	}

	protected static function errorResponse($message, DecisionCoreState $state, $code=500) {
		$state->response->setBody($message);
		return static::respond($code, $state);
	}

	protected static function decisionTest($test, $testVal, $trueFlow, $falseFlow, DecisionCoreState $state) {
		if ($test instanceof Exception) {
			return static::errorResponse($test->getMessage(), $state);
		}
		elseif (is_array($test)) {
			if ($test[0] == 'halt') {
				return static::respond($test[1], $state);
			}
		}
		elseif (is_callable($testVal)) {
			$result = $testVal($test);
			if ($test === true) {
				return static::decisionFlow($trueFlow, $test, $state);
			}
			else {
				return static::decisionFlow($falseFlow, $test, $state);
			}
		}
		else {
			if ($test == $testVal) {
				return static::decisionFlow($trueFlow, $test, $state);
			}
			else {
				return static::decisionFlow($falseFlow, $test, $state);
			}
		}
	}

	protected static function decisionFlow($flow, $testResult, DecisionCoreState $state) {
		if (is_integer($flow)) {
			if ($flow >= 500 ) {
				return static::errorResponse('Expected: ' . $testResult, $state, $flow);
			}
			else {
				return static::respond($flow, $state);
			}
		}
		else {
			return $flow;
		}
	}

	/**
	 * Service Available
	 * @param  DecisionCoreState  $request
	 * @return string
	 */
	protected static function decision_v3b13(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('ping', $state), 'pong', 'v3b13b', 503, $state);
	}
	protected static function decision_v3b13b(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('serviceAvailable', $state), true, 'v3b12', 503, $state);
	}

	/**
	 * Known method
	 */
	protected static function decision_v3b12(DecisionCoreState $state) {
		$methods = static::callResource('allowedMethods', $state);
		return static::decisionTest(in_array($state->request->getMethod(), $methods), true, 'v3b11', 405, $state);
	}

	/**
	 * URI too long?
	 */
	protected static function decision_v3b11(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('uriTooLong', $state), true, 414, 'v3b10', $state);
	}

	/**
	 * Method allowed?
	 */
	protected static function decision_v3b10(DecisionCoreState $state) {
		$methods = static::callResource('allowedMethods', $state);
		if (in_array($state->request->getMethod(), $methods)) {
			return 'v3b9';
		}
		else {
			$state->response->setHeader('Allow', implode(', ', $method));
			return static::respond(405, $state);
		}
	}

	/**
	 * Malformed?
	 */
	protected static function decision_v3b9(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('malformedRequest', $state), true, 400, 'v3b8', $state);
	}

	/**
	 * Authorized?
	 */
	protected static function decision_v3b8(DecisionCoreState $state) {
		$isAuthorized = static::callResource('isAuthorized', $state);
		if($isAuthorized === true) {
			return 'v3b7';
		}
		elseif ($isAuthorized instanceof Exception) {
			return static::errorResponse($isAuthorized->getMessage(), $state);
		}
		else {
			$state->response->setHeader('WWW-Authenticate', $isAuthorized);
			return static::respond(401, $state);
		}
	}

	/**
	 * Forbidden?
	 */
	protected static function decision_v3b7(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('forbidden', $state), true, 403, 'v3b6', $state);
	}

	/**
	 * Okay Content-* Headers?
	 */
	protected static function decision_v3b6(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('validContentHeaders', $state), true, 'v3b5', 501, $state);
	}

	/**
	 * Known Content-Type?
	 */
	protected static function decision_v3b5(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('knownContentType', $state), true, 'v3b4', 415, $state);
	}

	/**
	 * Req Entity Too Large?
	 */
	protected static function decision_v3b4(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('validEntityLength', $state), true, 'v3b3', 413, $state);
	}

	/**
	 * OPTIONS?
	 */
	protected static function decision_v3b3(DecisionCoreState $state) {
		$method = $state->request->getMethod();
		if ($method == 'OPTIONS') {
			$headers = static::callResource('options', $state);
			$state->response->setHeaders($headers);
			return static::respond(200, $state);
		}
		else {
			return 'v3c3';
		}
	}

	/**
	 * Accept exists?
	 */
	protected static function decision_v3c3(DecisionCoreState $state) {
		$accept = $state->request->getHeader('accept');
		if ($accept === null) {
			$types = static::callResource('contentTypesProvided', $state);
			$keys = array_keys($types);
			$state->response->setMetadataItem('content-type', $keys[0]);
			return 'v3d4';
		}
		else {
			return 'v3c4';
		}
	}

	/**
	 * Acceptable media type available?
	 */
	protected static function decision_v3c4(DecisionCoreState $state) {
		$types = static::callResource('contentTypesProvided', $state);
		$accept = $state->request->getHeader('accept');

		// choose media type
		$type = null;

		if ($type === null) {
			return static::respond(406, $state);
		}
		else {
			$state->response->setMetadataItem('content-type', $type);
			return 'v3d4';
		}
	}

	/**
	 * Accept-Language exists?
	 */
	protected static function decision_v3d4(DecisionCoreState $state) {
		return static::decisionTest($state->request->getHeader('accept-language'), null, 'v3e5', 'v3d5', $state);
	}

	/**
	 * Acceptable Language available?
	 */
	protected static function decision_v3d5(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('languageAvailable', $state), null, 'v3e5', 406, $state);
	}

	/**
	 * Accept-Charset exists?
	 */
	protected static function decision_v3e5(DecisionCoreState $state) {
		$charset = $state->request->getHeader('accept-charset');
		if ($charset === null) {
			// TODO choose a charset
			$charset = true;
			return static::decisionTest($charset, false, 406, 'v3f6', $state);
		}
		else {
			return 'v3e6';
		}
	}

	/**
	 * Accept-Encoding exists?
	 */
	protected static function decision_v3f6(DecisionCoreState $state) {
		$contentType = $state->response->getMetadataItem('content-type');
		$charset = $state->response->getMetadataItem('chosen-charset');

		if ($charset === null) {
			$charset = '';
		}
		else {
			$charset = '; charset=' . $charset;
		}
		
		$state->response->setHeader('Content-Type', $charset);

		$encoding = $state->request->getHeader('accept-encoding');
		if ($encoding === null) {
			// TODO choose an encoding
			return static::decisionTest('identity;q=1.0,*;q=0.5', false, 406, 'v3g7', $state);
		}
		else {
			$state->response->setHeader('content-type', $type);
			return 'v3f7';
		}
	}

	/**
	 * Acceptable encoding available?
	 */
	protected static function decision_v3f7(DecisionCoreState $state) {
		// TODO choose encoding
		$encoding = $state->request->getHeader('accept-encoding');
		return static::decisionTest($encoding, false, 406, 'v3g7', $state);
	}

	/**
	 * Resource exists?
	 */
	protected static function decision_v3g7(DecisionCoreState $state) {
		// TODO variances
		$variances = null;
		if (is_array($variances) && count($variances)) {
			_wrcall(array('set_resp_header', 'Vary', implode(', ', $variances)));
		}
		return static::decisionTest(static::callResource('resourceExists', $state), true, 'v3g8', 'v3h7', $state);
	}

	/**
	 * If-Match exists?
	 */
	protected static function decision_v3g8(DecisionCoreState $state) {
		return static::decisionTest($state->request->getHeader('if-match'), null, 'v3h10', 'v3g9', $state);
	}

	/**
	 * If-Match: * exists?
	 */
	protected static function decision_v3g9(DecisionCoreState $state) {
		return static::decisionTest($state->request->getHeader('if-match'), '*', 'v3h10', 'v3g11', $state);
	}

	/**
	 * ETag in If-Match
	 */
	protected static function decision_v3g11(DecisionCoreState $state) {
		$etags = $request->getHeader('if-match');
		// TODO parse etags
		return static::decisionTest(
				static::callResource('generateEtag', $state), 
					function($etag) use ($etags) { return in_array($etag, $etags); },
					'v3h10', 412, $state);
	}

	/**
	 * If-Match exists?
	 */
	protected static function decision_v3h7(DecisionCoreState $state) {
		return static::decisionTest($state->request->getHeader('if-match'), null, 'v3i7', 412, $state);
	}

	/**
	 * If-unmodified-since exists?
	 */
	protected static function decision_v3h10(DecisionCoreState $state) {
		return static::decisionTest($state->request->getHeader('if-unmodified-since'), null, 'v3i12', 'v3i11', $state);
	}

	/**
	 * I-UM-S is valid date?
	 */
	protected static function decision_v3h11(DecisionCoreState $state) {
		$date = $state->request->getHeader('if-unmodified-since');
		// Check date format
		$result = true;
		return static::decisionTest($result, false, 'v3i12', 'v3h12', $state);
	}

	/**
	 * Last-Modified > I-UM-S?
	 */
	protected static function decision_v3h12(DecisionCoreState $state) {
		$date = $state->request->getHeader('if-unmodified-since');
		// Convert
		$reqPhpDate = 0;
		$resPhpDate = static::callResource('lastModified', $state);
		return static::decisionTest($resPhpDate > $reqPhpDate, true, 412, 'v3i12', $state);
	}

	/**
	 * Moved permanently? (apply PUT to different URI)
	 */
	protected static function decision_v3i4(DecisionCoreState $state) {
		$moved = static::callResource('movedPermanently', $state);
		if ($moved === true) {
			return 'v3p3';
		}
		elseif ($moved instanceof Exception) {
			return static::errorResponse();
		}
		elseif (is_array($moved)) {
			if ($moved[0] == 'halt') {
				return static::respond($moved[1], $state);
			}
		}
		else {
			$state->response->setHeader('Location', $moved);
			return static::respond(301, $state);
		}
	}

	/**
	 * PUT?
	 */
	protected static function decision_v3i7(DecisionCoreState $state) {
		return static::decisionTest($state->request->getMethod(), 'PUT', 'v3i4', 'v3k7', $state);
	}

	/**
	 * If-None-Match exists?
	 */
	protected static function decision_v3i12(DecisionCoreState $state) {
		return static::decisionTest($state->request->getHeader('if-none-match'), null, 'v3l13', 'v3i13', $state);
	}

	/**
	 * If-None-Match: * exists?
	 */
	protected static function decision_v3i13(DecisionCoreState $state) {
		return static::decisionTest($state->request->getHeader('if-none-match'), '*', 'v3j18', 'v3k13', $state);
	}

	/**
	 * GET or HEAD?
	 */
	protected static function decision_v3j18(DecisionCoreState $state) {
		return static::decisionTest(in_array($state->request->getMethod(), array('GET', 'HEAD')), true, 304, 412, $state);
	}

	/**
	 * Moved permanently?
	 */
	protected static function decision_v3k5(DecisionCoreState $state) {
		$moved = static::callResource('movedPermanently', $state);
		if ($moved === false) {
			return 'v3l5';
		}
		elseif ($moved instanceof Exception) {
			return static::errorResponse($moved->getMessage(), $state);
		}
		elseif (is_array($moved)) {
			if ($moved[0] == 'halt') {
				return static::respond($moved[1], $state);
			}
		}
		else {
			$state->response->setHeader('Location', $moved);
			return static::respond(301, $state);
		}
	}

	/**
	 * Previously existed?
	 */
	protected static function decision_v3k7(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('previouslyExisted', $state), true, 'v3k5', 'v3l7', $state);
	}

	/**
	 * Etag in if-none-match?
	 */
	protected static function decision_v3k13(DecisionCoreState $state) {
		$etags = $state->request->getHeader('if-none-match');
		// TODO parse etags
		return static::decisionTest(
				static::callResource('genereateEtag', $state), 
					function($etag) use ($etags) { return in_array($etag, $etags); },
					'v3j18', 'v3l13', $state);
	}

	/**
	 * Moved temporarily?
	 */
	protected static function decision_v3l5(DecisionCoreState $state) {
		$moved = static::callResource('movedTemporarily', $state);
		if ($moved === false) {
			return 'v3m5';
		}
		elseif ($moved instanceof Exception) {
			return static::errorResponse($moved->getMessage(), $state);
		}
		elseif (is_array($moved)) {
			if ($moved[0] == 'halt') {
				return static::respond($moved[1], $state);
			}
		}
		else {
			$state->response->setHeader('Location', $moved);
			return static::respond(307, $state);
		}
	}

	/**
	 * POST?
	 */
	protected static function decision_v3l7(DecisionCoreState $state) {
		return static::decisionTest($state->request->getMethod(), 'POST', 'v3m7', 404, $state);
	}

	/**
	 * IMS exists?
	 */
	protected static function decision_v3l13(DecisionCoreState $state) {
		return static::decisionTest($state->request->getHeader('if-modified-since'), null, 'v3m16', 'v3l14', $state);
	}

	/**
	 * IMS is valid date?
	 */
	protected static function decision_v3l14(DecisionCoreState $state) {
		$date = $state->request->getHeader('if-unmodified-since');
		// Check date format
		$result = true;
		return static::decisionTest($result, false, 'v3m16', 'v3l15', $state);
	}

	/**
	 * IMS > Now?
	 */
	protected static function decision_v3h15(DecisionCoreState $state) {
		$nowDateTime = gmdate("Y-m-d\TH:i:s\Z");
		$requestDate = $state->request->getHeader("if-modified-since");
		// Convert date
		$reqPhpDate = 0;
		return static::decisionTest($reqPhpDate > $nowDateTime, true, 'v3m16', 'v3l17', $state);
	}

	/**
	 * Last-Modified > IMS?
	 */
	protected static function decision_v3l17(DecisionCoreState $state) {
		$date = $state->request->getHeader('if-unmodified-since');
		// Convert
		$reqPhpDate = 0;
		$resPhpDate = static::callResource('lastModified', $state);
		return static::decisionTest($resPhpDate === null || $resPhpDate > $reqPhpDate, true, 'v3m16', 304, $state);
	}

	/**
	 * POST?
	 */
	protected static function decision_v3m5(DecisionCoreState $state) {
		return static::decisionTest($state->request->getMethod(), 'POST', 'v3n5', 410, $state);
	}

	/**
	 * Server allows POST to missing resource?
	 */
	protected static function decision_v3m7(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('allowMissingPost', $rstate), true, 'v3n11', 404, $state);
	}

	/**
	 * DELETE?
	 */
	protected static function decision_v3m16(DecisionCoreState $state) {
		return static::decisionTest($state->request->getMethod(), 'DELETE', 'v3m20', 'v3n16', $state);
	}

	/**
	 * DELETE enacted immediately?
	 * Also where DELETE is forced.
	 */
	protected static function decision_v3m20(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('deleteResource', $state), true, 'v3m20b', 500, $state);
	}
	protected static function decision_v3m20b(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('deleteCompleted', $state), true, 'v3o20', 202, $state);
	}

	// TODO
	/**
	 * Redirect?
	 */
	protected static function decision_v3n11(DecisionCoreState $state) {
		$postIsCreate = static::callResource('postIsCreate', $state);
		return 'stop';
	}

	/**
	 * POST?
	 */
	protected static function decision_v3n16(DecisionCoreState $state) {
		return static::decisionTest($state->request->getMethod(), 'POST', 'v3n11', 'v3o16', $state);
	}

	/**
	 * Conflict?
	 */
	protected static function decision_v3o14(DecisionCoreState $state) {
		$isConflict = static::callResource('isConflict', $state);
		if ($isConflict === true) {
			return static::respond(409, $state);
		}
		else {
			$accept = static::acceptHelper();
			switch ($accept[0]) {
				case 'respond':
					return static::respond($accept[1], $state);
					break;
				case 'halt':
					return static::respond($accept[1], $state);
					break;
				case 'error':
					return static::errorResponse($accept[1], $state);
					break;
				
				default:
					return 'v3p11';
					break;
			}
		}
	}

	/**
	 * PUT?
	 */
	protected static function decision_v3o16(DecisionCoreState $state) {
		return static::decisionTest($state->request->getMethod(), 'PUT', 'v3o14', 'v3o18', $state);
	}

	// TODO
	/**
	 * Multiple representations?
	 * (also where body generation for GET and HEAD is done)
	 */
	protected static function decision_v3o18(DecisionCoreState $state) {
		$method = $state->request->getMethod();
		$buildBody = ($method == 'GET' || $method == 'HEAD');

		$finalBody = null;

		if ($buildBody === true) {
			$etag = static::callResource('generateEtag', $state);
			// TODO quoted string
			if ($etag) {
				$state->response->setHeader('ETag', $etag);
			}
			
			$contentType = $state->response->getMetadataItem('content-type');

			$lastModified = static::callResource('lastModified', $state);

			if ($lastModified) {
				// TODO convert date
				$state->response->setHeader('Last-Modified', $lastModified);
			}

			$expires = static::callResource('expires', $state);

			if ($expires) {
				// TODO convert date
				$state->response->setHeader('Expires', $lastModified);
			}

			$contentTypesProvided = static::callResource('contentTypesProvided', $state);
			foreach ($contentTypesProvided as $ct => $fun) {
				if ($ct == $contentType) {
					$finalBody = static::callResource($fun, $state);
					break;
				}
			}

		}

		if ($finalBody === null) {
			return 'v3o18b';
		}
		elseif ($finalBody instanceof Exception) {
			return static::errorResponse($finalBody->getMessage(), $state);
		}
		elseif (is_array($finalBody)) {
			if ($finalBody[0] == 'halt') {
				return static::respond($finalBody[1], $state);
			}
		}
		else {
			$state->response->setBody(static::encodeBody($finalBody, $state));
			return 'v3o18b';
		}
	}
	protected static function decision_v3o18b(DecisionCoreState $state) {
		return static::decisionTest(static::callResource('multipleChoices', $state), true, 300, 200, $state);
	}

	/**
	 * Response includes an entity?
	 */
	protected static function decision_v3o20(DecisionCoreState $state) {
		return static::decisionTest($state->response->getBody(), null, 204, 'v3o18', $state);
	}

	/**
	 * Conflict?
	 */
	protected static function decision_v3p3(DecisionCoreState $state) {
		$isConflict = static::callResource('isConflict', $state);
		if ($isConflict === true) {
			return static::respond(409, $state);
		}
		else {
			$accept = static::acceptHelper();
			switch ($accept[0]) {
				case 'respond':
					return static::respond($accept[1], $state);
					break;
				case 'halt':
					return static::respond($accept[1], $state);
					break;
				case 'error':
					return static::errorResponse($accept[1], $state);
					break;
				
				default:
					return 'v3p11';
					break;
			}
		}
	}

	/**
	 * New resource?
	 * (at this point boils down to "has location header")
	 */
	protected static function decision_v3p11(DecisionCoreState $state) {
		$location = $state->resource->getHeader('Location');
		return ($location===null)?'v3o20':static::respond(201, $state);
	}

	protected static function encodeBody($body, DecisionCoreState $state) {
		$charset = $state->response->getMetadataItem('chosen-charset');
		return $body;
	}
}


class DecisionCoreState {

	/**
	 * Constructor for the decision state
	 * @param string   $resource
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $resource
	 */
	function __construct($resource, Request $request, Response $response, array &$context) {
		$this->resource = $resource;
		$this->request = $request;
		$this->response = $response;
		$this->context = $context;
	}

	/**
	 * @var Request
	 */
	public $request;
	/**
	 * @var Response
	 */
	public $response;
	/**
	 * @var array
	 */
	public $context;
	/**
	 * @var string
	 */
	public $resource;
}