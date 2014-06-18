<?php namespace PHPMachine;

/**
	The minimal interface required by any request that is to be used in the
	decision graph.
 */
interface Request {
	/**
  	Returns the URI for this request object
   */
	public function uri();

	/**
  	Returns the hostname to which the request was addressed
   */
	public function host();

	/**
		Returns the HTTP method used for the request
	 */
	public function method();

	/**
  	Returns the value of the header represented by $key.

  	If the header is not present and $fallback is provided, then $fallback
		is returned, otherwise null is returned.
 	 */
	public function header($key, $fallback = null);
}
