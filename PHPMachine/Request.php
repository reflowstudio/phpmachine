<?php

namespace PHPMachine;

abstract class Request {

	/**#@+
     * @const string METHOD constant names
     */
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET     = 'GET';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_TRACE   = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';
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
    protected $method = self::METHOD_GET;

    /**
     * @var string
     */
    protected $uri = null;

    /**
     * @var string
     */
    protected $version = self::VERSION_11;

    /**
     * @var array
     */
    protected $queryParams = null;

    /**
     * @var array
     */
    protected $postParams = null;

    /**
     * @var array
     */
    protected $fileParams = null;

    /**
     * @var string
     */
    protected $host = null;

    /**
     * @var int
     */
    protected $port = 80;

    /**
     * @var string
     */
    protected $scheme = false;

    /**
     * @var array
     */
    protected $envParams = null;

    /**
     * @var array
     */
    protected $headers = null;

    /**
     * Set the method for this request
     *
     * @param string $method
     * @return Request
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);
        if (!defined('static::METHOD_'.$method)) {
            throw new Exception('Invalid HTTP method passed');
        }
        $this->method = $method;
        return $this;
    }

    /**
     * Return the method for this request
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the URI/URL for this request, this can be a string or an instance of Zend\Uri\Http
     *
     * @throws Exception
     * @param string $uri
     * @return Request
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Return the URI for this request object
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set the HTTP version for this object, one of 1.0 or 1.1 (Request::VERSION_10, Request::VERSION_11)
     *
     * @throws Exception
     * @param string $version (Must be 1.0 or 1.1)
     * @return Request
     */
    public function setVersion($version)
    {
        if (!in_array($version, array(self::VERSION_10, self::VERSION_11))) {
            throw new Exception('Version provided is not a valid version for this HTTP request object');
        }
        $this->version = $version;
        return $this;
    }

    /**
     * Return the HTTP version for this request
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Provide an alternate Parameter Container implementation for query parameters in this object, (this is NOT the
     * primary API for value setting, for that see query())
     *
     * @param array $query
     * @return Request
     */
    public function setQuery(array $query)
    {
        $this->queryParams = $query;
        return $this;
    }

    /**
     * Return the parameter container responsible for query parameters
     *
     * @return array
     */
    public function query()
    {
        if ($this->queryParams === null) {
            $this->queryParams = array();
        }

        return $this->queryParams;
    }

    /**
     * Provide an alternate Parameter Container implementation for post parameters in this object, (this is NOT the
     * primary API for value setting, for that see post())
     *
     * @param array $post
     * @return Request
     */
    public function setPost(array $post)
    {
        $this->postParams = $post;
        return $this;
    }

    /**
     * Return the parameter container responsible for post parameters
     *
     * @return array
     */
    public function post()
    {
        if ($this->postParams === null) {
            $this->postParams = array();
        }

        return $this->postParams;
    }

    /**
     * Return the Cookie header, this is the same as calling $request->headers()->get('Cookie');
     *
     * @convenience $request->headers()->get('Cookie');
     * @return Header\Cookie
     */
    public function cookie()
    {
        return $this->headers()->get('Cookie');
    }

    /**
     * Provide an alternate Parameter Container implementation for file parameters in this object, (this is NOT the
     * primary API for value setting, for that see file())
     *
     * @param array $files
     * @return Request
     */
    public function setFile(array $files)
    {
        $this->fileParams = $files;
        return $this;
    }

    /**
     * Return the parameter container responsible for file parameters
     *
     * @return ParametersDescription
     */
    public function file()
    {
        if ($this->fileParams === null) {
            $this->fileParams = array();
        }

        return $this->fileParams;
    }

    /**
     *
     * @param $host
     * @return Request
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getHost()
    {
        if ($this->host === null) {
            $this->host = '127.0.0.1';
        }

        return $this->host;
    }

    /**
     * Provide an alternate Parameter Container implementation for server parameters in this object, (this is NOT the
     * primary API for value setting, for that see server())
     *
     * @param array $server
     * @return Request
     */
    public function setServer(array $server)
    {
        $this->serverParams = $server;
        return $this;
    }

    /**
     * Return the parameter container responsible for server parameters
     *
     * @see http://www.faqs.org/rfcs/rfc3875.html
     * @return \Zend\Stdlib\ParametersDescription
     */
    public function server()
    {
        if ($this->serverParams === null) {
            $this->serverParams = array();
        }

        return $this->serverParams;
    }

    /**
     * Provide an alternate Parameter Container implementation for env parameters in this object, (this is NOT the
     * primary API for value setting, for that see env())
     *
     * @param array $env
     * @return \PHPMachine\Http\Request
     */
    public function setEnv(array $env)
    {
        $this->envParams = $env;
        return $this;
    }

    /**
     * Return the parameter container responsible for env parameters
     *
     * @return array
     */
    public function env()
    {
        if ($this->envParams === null) {
            $this->envParams = array();
        }

        return $this->envParams;
    }

    /**
     * Provide an alternate Parameter Container implementation for headers in this object, (this is NOT the
     * primary API for value setting, for that see headers())
     *
     * @param array $headers
     * @return \PHPMachine\Http\Request
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Return the header container responsible for headers
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

    public function getHeader($key) {
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }
        else {
            return null;
        }
    }

    /**
     * Is this an OPTIONS method request?
     *
     * @return bool
     */
    public function isOptions()
    {
        return ($this->method === self::METHOD_OPTIONS);
    }

    /**
     * Is this a GET method request?
     *
     * @return bool
     */
    public function isGet()
    {
        return ($this->method === self::METHOD_GET);
    }

    /**
     * Is this a HEAD method request?
     *
     * @return bool
     */
    public function isHead()
    {
        return ($this->method === self::METHOD_HEAD);
    }

    /**
     * Is this a POST method request?
     *
     * @return bool
     */
    public function isPost()
    {
        return ($this->method === self::METHOD_POST);
    }

    /**
     * Is this a PUT method request?
     *
     * @return bool
     */
    public function isPut()
    {
        return ($this->method === self::METHOD_PUT);
    }

    /**
     * Is this a DELETE method request?
     *
     * @return bool
     */
    public function isDelete()
    {
        return ($this->method === self::METHOD_DELETE);
    }

    /**
     * Is this a TRACE method request?
     *
     * @return bool
     */
    public function isTrace()
    {
        return ($this->method === self::METHOD_TRACE);
    }

    /**
     * Is this a CONNECT method request?
     *
     * @return bool
     */
    public function isConnect()
    {
        return ($this->method === self::METHOD_CONNECT);
    }

    /**
     * Return the formatted request line (first line) for this http request
     *
     * @return string
     */
    public function renderRequestLine()
    {
        return $this->method . ' ' . (string) $this->uri . ' HTTP/' . $this->version;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $str = $this->renderRequestLine() . "\r\n";
        if ($this->headers) {
            $str .= $this->headers->toString();
        }
        $str .= "\r\n";
        $str .= $this->getContent();
        return $str;
    }

    /**
     * Allow PHP casting of this object
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

}