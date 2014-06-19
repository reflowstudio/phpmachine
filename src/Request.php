<?php namespace PHPMachine;

# Represents a HTTP request
class Request {
	private $headers = array();

	protected static function parse_headers() {
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$name = str_replace(' ', '-', ucwords(
					strtolower(str_replace('_', ' ', substr($name, 5)))
				));
				$headers[$name] = $value;
			} else if ($name == "CONTENT_TYPE") {
				$headers["Content-Type"] = $value;
			} else if ($name == "CONTENT_LENGTH") {
				$headers["Content-Length"] = $value;
			}
		}
		return $headers;
	}

	function __construct() {
		$this->headers = static::parse_headers();
	}

	/// Returns true if the object represents a HTTP POST request.
	function is_post() { return $_SERVER['REQUEST_METHOD'] === 'POST'; }

	/// Returns true if the object represents a HTTP GET request.
	function is_get() { return $_SERVER['REQUEST_METHOD'] === 'GET'; }

	/// Returns true if the request has been made via AJAX.
	function is_ajax() {
		$request_type = strtolower(
			get_in($this->headers, 'HTTP_X_REQUESTED_WITH', 'not_ajax')
		);
		return $request_type === 'xmlhttprequest';
	}

	/// Returns true if the object represnts a HTTPS request.
	function is_secure() {
		return ! (empty($server['https']) || $server['https'] == 'off');
	}

	/**
		Returns the query parameter data associated with the request at $key.

		If $key is not present in the query data, $fallback is returned. If $key is
		not specified or null, an associative array of all query data key, value pairs
		is returned.
	 */
	function query_param($key, $fallback = null) {
		return get_in($_GET, $key, $fallback);
	}

	/**
		Returns the post data associated with the request at $key.

		If $key is not present in the post data, $fallback is returned. If $key is
		not specified or null, an associative array of all post data key, value pairs
		is returned.
	 */
	function post_data($key = null, $fallback = null) {
		return get_in($_POST, $key, $fallback);
	}

	/// Returns the langauge code for the request.
	function language() { return 'EN'; }

	/// Returns the requested path as a file path.
	function as_file_path($extension) {
		return $this->abs_path() . $extension;
	}

	/// Returns the requested path without the query parameters.
	function abs_path() { return strtok($_SERVER['REQUEST_URI'], '?'); }

	/// Returns the URI of the request.
	function uri() { return $_SERVER['REQUEST_URI']; }

	/// Returns the host to which the request was sent.
	function host() { return $_SERVER['SERVER_NAME']; }

	/// Returns the HTTP method with the request was submitted.
	function method() { return $_SERVER['REQUEST_METHOD']; }

	/// Returns the header addressed by $key or $fallback if it doesn't exist.
	function header($key, $fallback = null) {
		return get_in($this->headers, $key, $fallback);
	}
}

/**
  Returns the value at $path from within the possibly nested array, $nested.

  $path may be either a key, or an array of keys representing the path through
  $nested to the desired key. If $path does not address an existing key, then
  $fallback is returned if specified, otherwise null is returned. Passing a
  null value for $path will result in $nested being returned.
 */
function get_in($nested, $path, $fallback = null) {
	if (is_array($path)) {
		foreach ($path as $key) {
			if (isset($nested[$key])) {
				$nested = $nested[$key];
			} else {
				return $fallback;
			}
		}
	} else if (!is_null($path)) {
		$nested = isset($nested[$path]) ? $nested[$path] : $fallback;
	}
	return $nested;
}
