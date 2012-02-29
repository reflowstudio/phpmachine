<?php

namespace PHPMachine;

class Response {

    /**#@+
     * @const int Status codes
     */
    const STATUS_CODE_CUSTOM = 0;
    const STATUS_CODE_100 = 100;
    const STATUS_CODE_101 = 101;
    const STATUS_CODE_102 = 102;
    const STATUS_CODE_200 = 200;
    const STATUS_CODE_201 = 201;
    const STATUS_CODE_202 = 202;
    const STATUS_CODE_203 = 203;
    const STATUS_CODE_204 = 204;
    const STATUS_CODE_205 = 205;
    const STATUS_CODE_206 = 206;
    const STATUS_CODE_207 = 207;
    const STATUS_CODE_208 = 208;
    const STATUS_CODE_300 = 300;
    const STATUS_CODE_301 = 301;
    const STATUS_CODE_302 = 302;
    const STATUS_CODE_303 = 303;
    const STATUS_CODE_304 = 304;
    const STATUS_CODE_305 = 305;
    const STATUS_CODE_306 = 306;
    const STATUS_CODE_307 = 307;
    const STATUS_CODE_400 = 400;
    const STATUS_CODE_401 = 401;
    const STATUS_CODE_402 = 402;
    const STATUS_CODE_403 = 403;
    const STATUS_CODE_404 = 404;
    const STATUS_CODE_405 = 405;
    const STATUS_CODE_406 = 406;
    const STATUS_CODE_407 = 407;
    const STATUS_CODE_408 = 408;
    const STATUS_CODE_409 = 409;
    const STATUS_CODE_410 = 410;
    const STATUS_CODE_411 = 411;
    const STATUS_CODE_412 = 412;
    const STATUS_CODE_413 = 413;
    const STATUS_CODE_414 = 414;
    const STATUS_CODE_415 = 415;
    const STATUS_CODE_416 = 416;
    const STATUS_CODE_417 = 417;
    const STATUS_CODE_418 = 418;
    const STATUS_CODE_422 = 422;
    const STATUS_CODE_423 = 423;
    const STATUS_CODE_424 = 424;
    const STATUS_CODE_425 = 425;
    const STATUS_CODE_426 = 426;
    const STATUS_CODE_428 = 428;
    const STATUS_CODE_429 = 429;
    const STATUS_CODE_431 = 431;
    const STATUS_CODE_500 = 500;
    const STATUS_CODE_501 = 501;
    const STATUS_CODE_502 = 502;
    const STATUS_CODE_503 = 503;
    const STATUS_CODE_504 = 504;
    const STATUS_CODE_505 = 505;
    const STATUS_CODE_506 = 506;
    const STATUS_CODE_507 = 507;
    const STATUS_CODE_508 = 508;
    const STATUS_CODE_511 = 511;
    
    /**#@-*/

    /**#@+
     * @const string Version constant numbers
     */
    const VERSION_11 = '1.1';
    const VERSION_10 = '1.0';
    /**#@-*/

    /**
     * @var string
     */
    protected $version = self::VERSION_11;

    /**
     * @var string | null
     */
    protected $body = null;

    /**
     * @var array Recommended Reason Phrases
     */
    protected $recommendedReasonPhrases = array(
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
     * @var int Status code
     */
    protected $statusCode = 200;

    /**
     * @var string|null Null means it will be looked up from the $reasonPhrase list above
     */
    protected $reasonPhrase = null;

    /**
     * @var array
     */
    protected $headers = null;

    /**
     * @var array
     */
    protected $metadata = null;

    /**
     * Render the status line header
     *
     * @return string
     */
    public function renderStatusLine()
    {
        $status = sprintf(
            'HTTP/%s %d %s',
            $this->getVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase()
        );
        return trim($status);
    }

    /**
     * Set response headers
     *
     * @param  array $headers
     * @return Response
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Set header
     *
     * @param  array $headers
     * @return Response
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Get response headers
     * 
     * @return array
     */
    public function getHeaders()
    {
        if ($this->headers === null) {
            $this->headers = array();
        }
        return $this->headers;
    }

    /**
     * Set response headers
     *
     * @param  array $headers
     * @return Response
     */
    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Get response metadata
     * 
     * @return array
     */
    public function getMetadata()
    {
        if ($this->metadata === null) {
            $this->metadata = array();
        }
        return $this->metadata;
    }

    /**
     * Set metadata item
     *
     * @param  array $headers
     * @return Response
     */
    public function setMetadataItem($key, $value)
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    /**
     * Get metadata item
     *
     * @param  array $headers
     * @return Response
     */
    public function getMetadataItem($key)
    {
    	if (isset($this->metadata[$key])) {
    		return $this->metadata[$key];
    	}
    	else {
    		return null;
    	}
    }

    /**
     * @param string $version
     * @return Response
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Retrieve HTTP status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param string $reasonPhrase
     * @return Response
     */
    public function setReasonPhrase($reasonPhrase)
    {
        $this->reasonPhrase = trim($reasonPhrase);
        return $this;
    }

    /**
     * Get HTTP status message
     *
     * @return string
     */
    public function getReasonPhrase()
    {
        if ($this->reasonPhrase == null) {
            return $this->recommendedReasonPhrases[$this->statusCode];
        }
        return $this->reasonPhrase;
    }

    /**
     * Set HTTP status code and (optionally) message
     *
     * @param numeric $code
     * @return Response
     */
    public function setStatusCode($code)
    {
        $const = get_called_class() . '::STATUS_CODE_' . $code;
        if (!is_numeric($code) || !defined($const)) {
            $code = is_scalar($code) ? $code : gettype($code);
            throw new Exception(sprintf(
                'Invalid status code provided: "%s"',
                $code
            ));
        }
        $this->statusCode = (int) $code;
        return $this;
    }

    /**
     * Get the body of the response
     * 
     * @return string 
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Get the body of the response
     * 
     * @return string 
     */
    public function setBody($body) {
        $this->body = $body;
        return $this;
    }
    
    /**
     * Does the status code indicate a client error?
     *
     * @return bool
     */
    public function isClientError()
    {
        $code = $this->getStatusCode();
        return ($code < 500 && $code >= 400);
    }

    /**
     * Is the request forbidden due to ACLs?
     *
     * @return bool
     */
    public function isForbidden()
    {
        return (403 == $this->getStatusCode());
    }

    /**
     * Is the current status "informational"?
     *
     * @return bool
     */
    public function isInformational()
    {
        $code = $this->getStatusCode();
        return ($code >= 100 && $code < 200);
    }

    /**
     * Does the status code indicate the resource is not found?
     *
     * @return bool
     */
    public function isNotFound()
    {
        return (404 === $this->getStatusCode());
    }

    /**
     * Do we have a normal, OK response?
     *
     * @return bool
     */
    public function isOk()
    {
        return (200 === $this->getStatusCode());
    }

    /**
     * Does the status code reflect a server error?
     *
     * @return bool
     */
    public function isServerError()
    {
        $code = $this->getStatusCode();
        return (500 <= $code && 600 > $code);
    }

    /**
     * Do we have a redirect?
     * 
     * @return bool 
     */
    public function isRedirect()
    {
        $code = $this->getStatusCode();
        return (300 <= $code && 400 > $code);
    }
    
    /**
     * Was the response successful?
     *
     * @return bool
     */
    public function isSuccess()
    {
        $code = $this->getStatusCode();
        return (200 <= $code && 300 > $code);
    }

    
    /**
     * Render entire response as HTTP response string
     * 
     * @return string
     */
    public function toString()
    {
        $str  = $this->renderStatusLine() . "\r\n";
        foreach ($this->getHeaders() as $key => $value) {
        	$str .= $key . "\t\t" . $value . "\n";
        }
        $str .= "\r\n";
        $str .= $this->getBody();
        return $str;
    }

    public function __toString()
    {
    	return $this->toString();
    }

}
