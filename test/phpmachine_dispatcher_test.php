<?php

include dirname(__FILE__) .'/../src/phpmachine_dispatcher.php';

class phpmachine_dispatcher_test extends PHPUnit_Framework_TestCase {

	public function test_calculate_app_root() {
        $this->assertEquals('.', phpmachine_dispatcher\_calculate_app_root(1));
        $this->assertEquals('../..', phpmachine_dispatcher\_calculate_app_root(2));
        $this->assertEquals('../../..', phpmachine_dispatcher\_calculate_app_root(3));
        $this->assertEquals('../../../..', phpmachine_dispatcher\_calculate_app_root(4));
    }

	public function test_reconstitute() {
        $this->assertEquals('', phpmachine_dispatcher\_reconstitute(array()));
        $this->assertEquals('foo', phpmachine_dispatcher\_reconstitute(array('foo')));
        $this->assertEquals('foo/bar', phpmachine_dispatcher\_reconstitute(array('foo', 'bar')));
        $this->assertEquals('foo/bar/baz', phpmachine_dispatcher\_reconstitute(array('foo', 'bar', 'baz')));
    }

	public function test_split_host() {
        $this->assertEquals(array('foo', 'bar', 'baz'), phpmachine_dispatcher\_split_host('foo.bar.baz'));
    }

	public function test_split_host_port() {
        $this->assertEquals(array(array(),80), phpmachine_dispatcher\_split_host_port(''));
        $this->assertEquals(array(array('foo', 'bar', 'baz'),80), phpmachine_dispatcher\_split_host_port('foo.bar.baz'));
        $this->assertEquals(array(array('foo', 'bar', 'baz'),8888), phpmachine_dispatcher\_split_host_port('foo.bar.baz:8888'));
    }

	public function test_bind_port_simple_match() {
        $this->assertEquals(array(), phpmachine_dispatcher\_bind_port(80, 80, array()));
        $this->assertEquals(array('foo' => 'bar'), 
        					phpmachine_dispatcher\_bind_port(1234, 1234, array('foo' => 'bar')));
    }

	public function test_bind_port_matchall() {
        $this->assertEquals(array(), phpmachine_dispatcher\_bind_port('*', 80, array()));
        $this->assertEquals(array('foo' => 'bar'), 
        					phpmachine_dispatcher\_bind_port('*', 1234, array('foo' => 'bar')));
    }

	public function test_bind_match() {
        $this->assertEquals(array('foo' => 80), phpmachine_dispatcher\_bind_port('foo', 80, array()));
        $wholeBinding = phpmachine_dispatcher\_bind_port('foo', 1234, array('bar' => 'baz'));
		$this->assertEquals(2, count($wholeBinding));
		$this->assertEquals(1234, $wholeBinding['foo']);
		$this->assertEquals('baz', $wholeBinding['bar']);
    }

	public function test_ind_port_fail() {
        $this->assertEquals(false, phpmachine_dispatcher\_bind_port(80, 1234, array()));
    }

	public function test_bind_path_empty() {
        $this->assertEquals(array(array(), array(), 0), phpmachine_dispatcher\_bind(array(), array(), array(), 0));
        $this->assertEquals(array(array(), array('x'=>'a'), 1), 
        				phpmachine_dispatcher\_bind(array(), array(), array('x'=>'a'), 1));
    }

	public function test_bind_matchall() {
        $this->assertEquals(array(array(), array(), 1), 
        				phpmachine_dispatcher\_bind(array('*'), array(), array(), 1));
        $this->assertEquals(array(array('a', 'b'), array(), 2), 
        				phpmachine_dispatcher\_bind(array('*'), array('a', 'b'), array(), 0));
    }

	public function test_path_fail_longer_match() {
        $this->assertEquals(false, phpmachine_dispatcher\_bind(array('x'), array(), array(), 0));
        $this->assertEquals(false, phpmachine_dispatcher\_bind(array('@foo'), array(), array(), 0));
    }

    public function test_bind_path_with_binding() {
        $this->assertEquals(array(array(), array('foo'=>'a'), 1), 
        				phpmachine_dispatcher\_bind(array('@foo'), array('a'), array(), 0));
        list($rest, $bind, $depth) = phpmachine_dispatcher\_bind(array('@foo', '*'), array('a', 'b'), array('bar'=>'baz'), 1);
		$this->assertEquals(array('b'), $rest);
		$this->assertEquals(3, $depth);
		$this->assertEquals(2, count($bind));
		$this->assertEquals('a', $bind['foo']);
		$this->assertEquals('baz', $bind['bar']);
    }

    public function test_bind_path_string_match() {
        $this->assertEquals(array(array(), array(), 1), 
        				phpmachine_dispatcher\_bind(array('a'), array('a'), array(), 0));
        $this->assertEquals(array(array(), array('foo'=>'bar'), 4), 
        				phpmachine_dispatcher\_bind(array('a', 'b', 'c'), array('a', 'b', 'c'), array('foo'=>'bar'), 1));
    }

    public function test_bind_path_string_fail() {
        $this->assertEquals(false, phpmachine_dispatcher\_bind(array('a'), array('b'), array(), 0));
        $this->assertEquals(false, phpmachine_dispatcher\_bind(array('a', 'b'), array('a', 'c'), array(), 0));
    }

    public function test_try_path_matching() {
    	$requestData = 'testing';
        $this->assertEquals(array('bar', 'baz', array(), array(), '.', ''), 
        	phpmachine_dispatcher\_try_path_binding(array(array(array('foo'), 'bar', 'baz')), array('foo'), array(), 80, array(), 0, $requestData));

        $dispatchList = array(
        	array(array('a', '@x'), 'foo', 'bar'),
        	array(array('b', '@y'), 'baz', 'quux'),
        	array(array('a', '@y', '*'), 'baz2', 'quux2')
        );

        $this->assertEquals(array('foo', 'bar', array(), array('x'=>'c'), '../..', ''), 
        	phpmachine_dispatcher\_try_path_binding($dispatchList, array('a','c'), array(), 80, array(), 0, $requestData));
        $this->assertEquals(array('baz', 'quux', array(), array('y'=>'c'), '../..', ''), 
        	phpmachine_dispatcher\_try_path_binding($dispatchList, array('b','c'), array(), 80, array(), 0, $requestData));
        $this->assertEquals(array('baz2', 'quux2', array('z'), array('y'=>'c'), '../../..', 'z'), 
        	phpmachine_dispatcher\_try_path_binding($dispatchList, array('a','c','z'), array(), 80, array(), 0, $requestData));
        $this->assertEquals(array('baz2', 'quux2', array('z','v'), array('y'=>'c'), '../../../..', 'z/v'), 
        	phpmachine_dispatcher\_try_path_binding($dispatchList, array('a','c','z','v'), array(), 80, array(), 0, $requestData));
    }

    public function test_try_path_fail() {
    	$requestData = 'testing';
        $this->assertEquals(array(false, array('a')), 
        	phpmachine_dispatcher\_try_path_binding(array(array(array('b'), 'x', 'y')), array('a'), array(), 80, array(), 0, $requestData));
    }

    public function test_dispatch() {
    	$requestData = 'testing';

    	$trueFun = function(){ return true; };
    	$falseFun = function(){ return false; };

        $this->assertEquals(array('x', 'y', array(), 80, array(), array(), '../../..', ''), 
        	phpmachine_dispatcher\dispatch('', 'a/b/c', array(array(array('a','b','c'),'x','y')), $requestData));

        $this->assertEquals(array('x', 'y', array(), 80, array(), array(), '../../..', ''), 
        	phpmachine_dispatcher\dispatch('', 'a/b/c', array(array(array('a','b','c'),$trueFun,'x','y')), $requestData));

        $this->assertEquals(array(false, array(array(), 80), array('a', 'b', 'c')), 
        	phpmachine_dispatcher\dispatch('', 'a/b/c', array(array(array('a','b','c'),$falseFun,'x','y')), $requestData));

        $dispatchList = array(array(array('foo', 'bar'), 80), array(array('a','b','c'),'x','y'));

        $this->assertEquals(array('x', 'y', array(), 80, array(), array(), '../../..', ''), 
        	phpmachine_dispatcher\dispatch('foo.bar', 'a/b/c', $dispatchList, $requestData));

        $dispatchList2 = array(array(array('foo', 'bar'), 1234), array(array('a','b'),'x','y'));

        $this->assertEquals(array('x', 'y', array(), 1234, array(), array(), '../../..', ''), 
        	phpmachine_dispatcher\dispatch('foo.bar:1234', 'a/b/', $dispatchList2, $requestData));

        $dispatchList3 = array(array(array('foo', 'bar'), 80), array(array('a','b','c'),'x','y'));

        $this->assertEquals(array(false, array(array('bar', 'baz'), 8000), array('q', 'r')), 
        	phpmachine_dispatcher\dispatch('baz.bar:8000', 'q/r', $dispatchList3, $requestData));
    }

}
