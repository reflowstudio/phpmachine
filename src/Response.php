<?php namespace PHPMachine;

interface Response {
	/// HTTP Reason Phrases
	const REASON = array(
		// INFORMATIONAL CODES
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		// SUCCESS CODES
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-status',
		208 => 'Already Reported',
		// REDIRECTION CODES
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy', // Deprecated
		307 => 'Temporary Redirect',
		// CLIENT ERROR
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Large',
		415 => 'Unsupported Media Type',
		416 => 'Requested range not satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Unordered Collection',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		// SERVER ERROR
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version not supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		511 => 'Network Authentication Required',
	);

	/**
  	Adds each header in $headers to the response headers.

		$headers must be an asociative array of header names to values.
   */
	public function add_headers(array $headers);

	/**
  	Adds the $key, $value pair as a HTTP header.
   */
	public function add_header($key, $value);

	/**
  	Adds the $key, $value pair as metadata for the response.
   */
	public function add_metadata($key, $value);

	/**
  	Returns the metatdata value for $key.

		If no metadata is present with the corresponding $key then null
		is returned. If no key is specified or a null value is passed as
		$key then an associative array of all $key, $value metadata pairs
		is returned.
   */
	public function metadata($key = null);

	/**
  	Returns the HTTP status code of the respsonse.
   */
	public function status_code();

	/**
		Sets the HTTP status code for the response to be $code.
   */
	public function set_status_code($code);

	/**
		Returns true if the response body is blank.
   */
	public function is_blank();

	/**
  	Writes $body to the body of the response.
   */
	public function write($body);

	/**
		Serves the response to the client.
	 */
	public function serve();
}
