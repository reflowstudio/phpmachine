<?php

namespace PHPMachine\Http;

use \PHPMachine\Request as AbstractRequest;

class Request extends AbstractRequest {

    public function __construct() {
        $this->host = $_SERVER['SERVER_NAME'];
        $this->port = $_SERVER['SERVER_PORT'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->scheme = isset($_SERVER['HTTPS'])?'https':'http';
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->queryParams = $_GET;
        $this->headers = static::getAllHeaders();

        return $this;
    }

    protected static function getAllHeaders() {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            } else if ($name == "CONTENT_TYPE") {
                $headers["Content-Type"] = $value;
            } else if ($name == "CONTENT_LENGTH") {
                $headers["Content-Length"] = $value;
            }
        }
        return $headers;
    }

}