<?php

namespace Tests\PHPMachine;

class Request extends \PHPMachine\Request {

    public function __construct() {
    	$args = func_get_args();
        $this->host = $this->getArgValue($args, 0, 'localhost');
        $this->port = $this->getArgValue($args, 1, '80');
        $this->method = $this->getArgValue($args, 2, 'GET');
        $this->scheme = $this->getArgValue($args, 3, 'http');
        $this->uri = $this->getArgValue($args, 4, '/');
        $this->queryParams = $this->getArgValue($args, 5, array());
        $this->headers = $this->getArgValue($args, 6, array());

        return $this;
    }

    protected function getArgValue(array $args, $index, $default) {
    	return (isset($args[$index]))?$args[$index]:$default;
    }

}