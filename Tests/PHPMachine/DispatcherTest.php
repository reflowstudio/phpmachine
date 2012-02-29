<?php

namespace Tests\PHPMachine;

class DispatcherTest extends \PHPUnit_Framework_TestCase {

	public function test_calculate_app_root() {
        $this->assertEquals('.', DispatcherPublic::calculateAppRoot(1));
        $this->assertEquals('../..', DispatcherPublic::calculateAppRoot(2));
        $this->assertEquals('../../..', DispatcherPublic::calculateAppRoot(3));
        $this->assertEquals('../../../..', DispatcherPublic::calculateAppRoot(4));
    }

	public function test_reconstitute() {
        $this->assertEquals('', DispatcherPublic::reconstitute(array()));
        $this->assertEquals('foo', DispatcherPublic::reconstitute(array('foo')));
        $this->assertEquals('foo/bar', DispatcherPublic::reconstitute(array('foo', 'bar')));
        $this->assertEquals('foo/bar/baz', DispatcherPublic::reconstitute(array('foo', 'bar', 'baz')));
    }

	public function test_split_host() {
        $this->assertEquals(array('foo', 'bar', 'baz'), DispatcherPublic::splitHost('foo.bar.baz'));
    }

	public function test_split_host_port() {
        $this->assertEquals(array(array(),80), DispatcherPublic::splitHostPort(''));
        $this->assertEquals(array(array('foo', 'bar', 'baz'),80), DispatcherPublic::splitHostPort('foo.bar.baz'));
        $this->assertEquals(array(array('foo', 'bar', 'baz'),8888), DispatcherPublic::splitHostPort('foo.bar.baz:8888'));
    }

	public function test_bind_port_simple_match() {
        $this->assertEquals(array(), DispatcherPublic::bindPort(80, 80, array()));
        $this->assertEquals(array('foo' => 'bar'), 
        					DispatcherPublic::bindPort(1234, 1234, array('foo' => 'bar')));
    }

	public function test_bind_port_matchall() {
        $this->assertEquals(array(), DispatcherPublic::bindPort('*', 80, array()));
        $this->assertEquals(array('foo' => 'bar'), 
        					DispatcherPublic::bindPort('*', 1234, array('foo' => 'bar')));
    }

	public function test_bind_match() {
        $this->assertEquals(array('foo' => 80), DispatcherPublic::bindPort('foo', 80, array()));
        $wholeBinding = DispatcherPublic::bindPort('foo', 1234, array('bar' => 'baz'));
		$this->assertEquals(2, count($wholeBinding));
		$this->assertEquals(1234, $wholeBinding['foo']);
		$this->assertEquals('baz', $wholeBinding['bar']);
    }

	public function test_ind_port_fail() {
        $this->assertEquals(false, DispatcherPublic::bindPort(80, 1234, array()));
    }

	public function test_bind_path_empty() {
        $this->assertEquals(array(array(), array(), 0), DispatcherPublic::bind(array(), array(), array(), 0));
        $this->assertEquals(array(array(), array('x'=>'a'), 1), 
        				DispatcherPublic::bind(array(), array(), array('x'=>'a'), 1));
    }

	public function test_bind_matchall() {
        $this->assertEquals(array(array(), array(), 1), 
        				DispatcherPublic::bind(array('*'), array(), array(), 1));
        $this->assertEquals(array(array('a', 'b'), array(), 2), 
        				DispatcherPublic::bind(array('*'), array('a', 'b'), array(), 0));
    }

	public function test_path_fail_longer_match() {
        $this->assertEquals(false, DispatcherPublic::bind(array('x'), array(), array(), 0));
        $this->assertEquals(false, DispatcherPublic::bind(array('@foo'), array(), array(), 0));
    }

    public function test_bind_path_with_binding() {
        $this->assertEquals(array(array(), array('foo'=>'a'), 1), 
        				DispatcherPublic::bind(array('@foo'), array('a'), array(), 0));
        list($rest, $bind, $depth) = DispatcherPublic::bind(array('@foo', '*'), array('a', 'b'), array('bar'=>'baz'), 1);
		$this->assertEquals(array('b'), $rest);
		$this->assertEquals(3, $depth);
		$this->assertEquals(2, count($bind));
		$this->assertEquals('a', $bind['foo']);
		$this->assertEquals('baz', $bind['bar']);
    }

    public function test_bind_path_string_match() {
        $this->assertEquals(array(array(), array(), 1), 
        				DispatcherPublic::bind(array('a'), array('a'), array(), 0));
        $this->assertEquals(array(array(), array('foo'=>'bar'), 4), 
        				DispatcherPublic::bind(array('a', 'b', 'c'), array('a', 'b', 'c'), array('foo'=>'bar'), 1));
    }

    public function test_bind_path_string_fail() {
        $this->assertEquals(false, DispatcherPublic::bind(array('a'), array('b'), array(), 0));
        $this->assertEquals(false, DispatcherPublic::bind(array('a', 'b'), array('a', 'c'), array(), 0));
    }

    public function test_try_path_matching() {
    	$requestData = new Request();
        $this->assertEquals(array('bar', 'baz', array(), array(), '.', ''), 
        	DispatcherPublic::tryPathBinding(array(array(array('foo'), 'bar', 'baz')), array('foo'), array(), 80, array(), 0, $requestData));

        $dispatchList = array(
        	array(array('a', '@x'), 'foo', 'bar'),
        	array(array('b', '@y'), 'baz', 'quux'),
        	array(array('a', '@y', '*'), 'baz2', 'quux2')
        );

        $this->assertEquals(array('foo', 'bar', array(), array('x'=>'c'), '../..', ''), 
        	DispatcherPublic::tryPathBinding($dispatchList, array('a','c'), array(), 80, array(), 0, $requestData));
        $this->assertEquals(array('baz', 'quux', array(), array('y'=>'c'), '../..', ''), 
        	DispatcherPublic::tryPathBinding($dispatchList, array('b','c'), array(), 80, array(), 0, $requestData));
        $this->assertEquals(array('baz2', 'quux2', array('z'), array('y'=>'c'), '../../..', 'z'), 
        	DispatcherPublic::tryPathBinding($dispatchList, array('a','c','z'), array(), 80, array(), 0, $requestData));
        $this->assertEquals(array('baz2', 'quux2', array('z','v'), array('y'=>'c'), '../../../..', 'z/v'), 
        	DispatcherPublic::tryPathBinding($dispatchList, array('a','c','z','v'), array(), 80, array(), 0, $requestData));
    }

    public function test_try_path_fail() {
        $requestData = new Request();
        $this->assertEquals(array(false, array('a')), 
        	DispatcherPublic::tryPathBinding(array(array(array('b'), 'x', 'y')), array('a'), array(), 80, array(), 0, $requestData));
    }

    public function test_dispatch() {
        $requestData = new Request();

    	$trueFun = function(){ return true; };
    	$falseFun = function(){ return false; };

        $this->assertEquals(array('x', 'y', array(), 80, array(), array(), '../../..', ''), 
        	DispatcherPublic::dispatch('', 'a/b/c', array(array(array('a','b','c'),'x','y')), $requestData));

        $this->assertEquals(array('x', 'y', array(), 80, array(), array(), '../../..', ''), 
        	DispatcherPublic::dispatch('', 'a/b/c', array(array(array('a','b','c'),$trueFun,'x','y')), $requestData));

        $this->assertEquals(array(false, array(array(), 80), array('a', 'b', 'c')), 
        	DispatcherPublic::dispatch('', 'a/b/c', array(array(array('a','b','c'),$falseFun,'x','y')), $requestData));

        $dispatchList = array(array(array('foo', 'bar'), 80), array(array('a','b','c'),'x','y'));

        $this->assertEquals(array('x', 'y', array(), 80, array(), array(), '../../..', ''), 
        	DispatcherPublic::dispatch('foo.bar', 'a/b/c', $dispatchList, $requestData));

        $dispatchList2 = array(array(array('foo', 'bar'), 1234), array(array('a','b'),'x','y'));

        $this->assertEquals(array('x', 'y', array(), 1234, array(), array(), '../../..', ''), 
        	DispatcherPublic::dispatch('foo.bar:1234', 'a/b/', $dispatchList2, $requestData));

        $dispatchList3 = array(array(array('foo', 'bar'), 80), array(array('a','b','c'),'x','y'));

        $this->assertEquals(array(false, array(array('bar', 'baz'), 8000), array('q', 'r')), 
        	DispatcherPublic::dispatch('baz.bar:8000', 'q/r', $dispatchList3, $requestData));
    }

}

class DispatcherPublic extends \PHPMachine\Dispatcher {
    public static function splitHostPort($hostAsString) {
        return parent::splitHostPort($hostAsString);
    }

    public static function tryHostBinding(array $dispatchList, $host, $port, $path, $depth, \PHPMachine\Request $requestData) {
        return parent::tryHostBinding($dispatchList, $host, $port, $path, $depth, $requestData);
    }

    public static function bindPort($port_match, $port, $bindings) {
        return parent::bindPort($port_match, $port, $bindings);
    }

    public static function tryPathBinding($pathSpecs, $pathTokens, $hostRemainder, $port, $hostBindings, $extraDepth, \PHPMachine\Request $requestData) {
        return parent::tryPathBinding($pathSpecs, $pathTokens, $hostRemainder, $port, $hostBindings, $extraDepth, $requestData);
    }

    public static function runGuard($fun, \PHPMachine\Request $requestData) {
        return parent::runGuard($fun, $requestData);
    }

    public static function splitHost($hostAsString) {
        return parent::splitHost($hostAsString);
    }

    public static function bind($tokens, $matches, $bindings, $depth) {
        return parent::bind($tokens, $matches, $bindings, $depth);
    }

    public static function reconstitute($unmatchedTokens) {
        return parent::reconstitute($unmatchedTokens);
    }

    public static function calculateAppRoot($number) {
        return parent::calculateAppRoot($number);
    }

}
