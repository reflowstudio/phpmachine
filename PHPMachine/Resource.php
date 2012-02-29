<?php 

namespace PHPMachine;

abstract class Resource {
	
	/**
	 * Called on every request.  Should initialize anything
	 * for the request and return it in an array that will
	 * be passed through every callback
	 *
	 * @return array
	 */
	public static function init (Request $request) {
		return array();
	}

	public static function ping (Request $request, array &$context) {
		return 'pong';
	}
	
	/**
	 * Returning non-true values will result in 404 Not Found.
	 * @return boolean
	 */
	public static function resourceExists (Request $request, array &$context) {
		return true;
	}
	
	/**
	 * @return boolean
	 */
	public static function serviceAvailable (Request $request, array &$context) {
		return true;
	}
	
	/**
	 * If this returns anything other than true, the response 
	 * will be 401 Unauthorized. The return value will be 
	 * used as the value in the WWW-Authenticate header
	 * @return boolean | array
	 */
	public static function isAuthorized (Request $request, array &$context) {
		return true;
	}
	
	/**
	 * @return boolean
	 */
	public static function forbidden (Request $request, array &$context) {
		return false;
	}
	
	/**
	 * If the resource accepts POST requests to nonexistent 
	 * resources, then this should return true.
	 *
	 * @return boolean
	 */
	public static function allowMissingPost (Request $request, array &$context) {
		return true;
	}
	
	/**
	 * @return boolean
	 */
	public static function malformedRequest (Request $request, array &$context) {
		return false;
	}
	
	/**
	 * @return boolean
	 */
	public static function uriTooLong (Request $request, array &$context) {
		return false;
	}
	
	/**
	 * @return boolean
	 */
	public static function knownContentType (Request $request, array &$context) {
		return true;
	}
	
	/**
	 * @return boolean
	 */
	public static function validContentHeaders (Request $request, array &$context) {
		return true;
	}
	
	/**
	 * @return boolean
	 */
	public static function validEntityLength (Request $request, array &$context) {
		return true;
	}
	
	/**
	 * If the OPTIONS method is supported and is used, the return 
	 * value of this function is expected to be a list of pairs 
	 * representing header names and values that should appear in 
	 * the response.
	 * 
	 * @return array
	 */
	public static function options (Request $request, array &$context) {
		return array();
	}
	
	/**
	 * If a Method not in this list is requested, then a 405 
	 * Method Not Allowed will be sent. Note that these are 
	 * all-caps.
	 * 
	 * @return array
	 */
	public static function allowedMethods (Request $request, array &$context) {
		return array('OPTIONS', 'GET','POST','PUT','DELETE');
	}
	
	/**
	 * This is called when a DELETE request should be enacted, 
	 * and should return true if the deletion succeeded.
	 *
	 * @return boolean
	 */
	public static function deleteResource (Request $request, array &$context) {
		return true;
	}
	
	/**
	 * This is only called after a successful deleteResource call, 
	 * and should return false if the deletion was accepted but 
	 * cannot yet be guaranteed to have finished.
	 *
	 * @return boolean
	 */
	public static function deleteComplete (Request $request, array &$context) {
		return true;
	}
	
	/**
	 * If POST requests should be treated as a request to put content 
	 * into a (potentially new) resource as opposed to being a 
	 * generic submission for processing, then this function should 
	 * return true. If it does return true, then createPath will 
	 * be called and the rest of the request will be treated much 
	 * like a PUT to the Path entry returned by that call.
	 *
	 * @return boolean
	 */
	public static function postIsCreate (Request $request, array &$context) {
		return false;
	}
	
	/**
	 * This will be called on a POST request if postIsCreate returns 
	 * true. It is an error for this function to not produce a Path 
	 * if postIsCreate returns true. The Path returned should be a 
	 * valid URI part following the dispatcher prefix.
	 *
	 * @return string
	 */
	public static function createPath (Request $request, array &$context) {
		return '';
	}
	
	/**
	 * If postIsCreate returns false, then this will be called to 
	 * process any POST requests. If it succeeds, it should return true.
	 * 
	 * @return boolean
	 */
	public static function processPost (Request $request, array &$context) {
		return true;
	}
	
	/**
	 * This should return an associative of the  
	 * form {Mediatype, Handler} where Mediatype is a string of 
	 * content-type format and the Handler is an string naming the 
	 * function which can provide a resource representation in that 
	 * media type. Content negotiation is driven by this return value. 
	 * For example, if a client request includes an Accept header 
	 * with a value that does not appear as a first element in any 
	 * of the return tuples, then a 406 Not Acceptable will be sent.
	 *
	 * @return array
	 */
	public static function contentTypesProvided (Request $request, array &$context) {
		return array('text/html'=>'toHTML');
	}
	
	/**
	 * This is used similarly to contentTypesProvided, except that 
	 * it is for incoming resource representations – for example, 
	 * PUT requests.
	 *
	 * @return array
	 */
	public static function contentTypesAccepted (Request $request, array &$context) {
		return array('application/x-www-form-urlencoded'=>'fromForm');
	}
	
	/**
	 * If this is anything other than null, it must be an 
	 * associative array where each pair is of the form {Charset, Converter} 
	 * where Charset is a string naming a charset and Converter is a 
	 * callable function in the resource which will be called on the 
	 * produced body in a GET and ensure that it is in Charset.
	 *
	 * @return array | null
	 */
	public static function charsetsProvided (Request $request, array &$context) {
		return null;
	}
	
	/**
	 * This must be an associative array where in each pair Encoding is a 
	 * string naming a valid content encoding and Encoder is a callable 
	 * function in the resource which will be called on the produced 
	 * body in a GET and ensure that it is so encoded. One useful 
	 * setting is to have the function check on method, and on GET 
	 * requests return 
	 * array("identity"=>function($content){return $content;}, 
	 * 		"gzip"=>function($content){return gzencode($content);}, 
	 * as this is all that is needed to support gzip content encoding.
	 *
	 * @return array
	 */
	public static function encodingsProvided (Request $request, array &$context) {
		return array('identity'=>function($content){return $content;});
	}
	
	/**
	 * If this function is implemented, it should return a list of 
	 * strings with header names that should be included in a given 
	 * response’s Vary header. The standard conneg headers 
	 * (Accept, Accept-Encoding, Accept-Charset, Accept-Language) 
	 * do not need to be specified here as PHPMachine will add the 
	 * correct elements of those automatically depending on resource 
	 * behavior.
	 *
	 * @return array
	 */
	public static function variances (Request $request, array &$context) {
		return array();
	}
	
	/**
	 * If this returns true, the client will receive a 409 Conflict.
	 *
	 * @return boolean
	 */
	public static function isConflict (Request $request, array &$context) {
		return false;
	}
	
	/**
	 * If this returns true, then it is assumed that multiple 
	 * representations of the response are possible and a single 
	 * one cannot be automatically chosen, so a 300 Multiple Choices 
	 * will be sent instead of a 200.
	 *
	 * @return boolean
	 */
	public static function multipleChoices (Request $request, array &$context) {
		return false;
	}
	
	/**
	 * @return boolean
	 */
	public static function previouslyExisted (Request $request, array &$context) {
		return false;
	}
	
	/**
	 * @return boolean | string
	 */
	public static function movedPermanently (Request $request, array &$context) {
		return false;
	}
	
	/**
	 * @return boolean
	 */
	public static function movedTemporarily (Request $request, array &$context) {
		return false;
	}
	
	/**
	 * @return integer | null
	 */
	public static function lastModified (Request $request, array &$context) {
		return null;
	}
	
	/**
	 * @return integer | null
	 */
	public static function expires (Request $request, array &$context) {
		return null;
	}
	
	/**
	 * If this returns a value, it will be used as the value of the 
	 * ETag header and for comparison in conditional requests.
	 *
	 * @return integer | null
	 */
	public static function generateEtag (Request $request, array &$context) {
		return null;
	}
	
	/**
	 * This function, if exported, is called just before the final 
	 * response is constructed and sent. The Result is ignored, so 
	 * any effect of this function must be by returning a modified 
	 * ReqData.
	 *
	 * @return boolean
	 */
	public static function finishRequest (Request $request, array &$context) {
		return true;
	}

}