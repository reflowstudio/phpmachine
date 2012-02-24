<?php

namespace phpmachine;

class prq {

    private $method;
    private $scheme;
    private $version;
    private $rawPath;
    private $req_headers;
    private $pm_state;
    private $path;
    private $req_cookie;
    private $req_query;
    private $peer;
    private $req_body;
    private $max_recv_body;
    private $max_recv_hunk;
    private $app_root;
    private $path_info = array();
    private $host_tokens;
    private $port;
    private $path_tokens;
    private $disp_path;
    private $resp_redirect=false;
    private $resp_headers = array();
    private $resp_body;
    private $response_code = 500;
    private $notes = array();

    public function __construct($method, $scheme, $version, $rawPath, $headers) {
        $this->method = $method;
        $this->scheme = $scheme;
        $this->version = $version;
        $this->rawPath = $rawPath;
        $this->req_headers = $headers;
        $this->max_recv_body = (1024*(1024*1024));
        $this->max_recv_hunk = (64*(1024*1024));
    }

    public function load_dispatch_data($pathInfo, $hostTokens, $port, $pathTokens, $appRoot, $dispPath) {
        $this->path_info = $pathInfo;
        $this->host_tokens = $hostTokens;
        $this->port = $port;
        $this->path_tokens = $pathTokens;
        $this->app_root = $appRoot;
        $this->disp_path = $dispPath;
    }

    public function get_method() {
        return $this->method;
    }

    public function get_scheme() {
        return $this->scheme;
    }

    public function get_version() {
        return $this->version;
    }

    public function get_rawPath() {
        return $this->rawPath;
    }

    public function get_path() {
        return $this->path;
    }

    public function get_req_cookie() {
        return $this->req_cookie;
    }

    public function get_req_query() {
        return $this->req_query;
    }

    public function get_peer() {
        return $this->peer;
    }

    public function get_req_body() {
        return $this->req_body;
    }

    public function get_max_recv_body() {
        return $this->max_recv_body;
    }

    public function get_app_root() {
        return $this->app_root;
    }

    public function get_path_info() {
        return $this->path_info;
    }

    public function get_host_tokens() {
        return $this->host_tokens;
    }

    public function get_host() {
        return implode('.', ($this->host_tokens===null)?array():$this->host_tokens);
    }

    public function get_port() {
        return $this->port;
    }

    public function get_path_tokens() {
        return $this->path_tokens;
    }

    public function get_disp_path() {
        return $this->disp_path;
    }

    public function get_resp_redirect() {
        return $this->resp_redirect;
    }

    public function get_resp_headers() {
        return $this->resp_headers;
    }

    public function get_resp_body() {
        return $this->resp_body;
    }

    public function get_response_code() {
        return $this->response_code;
    }

    public function set_peer($peer) {
        $this->peer = $peer;
    }

    public function set_req_body($req_body) {
        $this->req_body = $req_body;
    }

    public function set_max_recv_body($max_recv_body) {
        $this->max_recv_body = $max_recv_body;
    }

    public function set_path_info($path_info) {
        $this->path_info = $path_info;
    }

    public function set_disp_path($disp_path) {
        $this->disp_path = $disp_path;
    }

    public function set_resp_headers($resp_headers) {
        $this->resp_headers = $resp_headers;
    }

    public function set_resp_body($resp_body) {
        $this->resp_body = $resp_body;
    }

    public function set_response_code($response_code) {
        $this->response_code = $response_code;
    }



}