<?php namespace PHPMachine;

interface Response {
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
  	Writes $content to the body of the response.
   */
	public function write($content);

	/**
		Serves the response to the client.
	 */
	public function serve();
}
