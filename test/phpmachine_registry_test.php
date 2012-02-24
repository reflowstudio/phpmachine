<?php

include dirname(__FILE__) .'/../src/phpmachine_registry.php';

class phpmachine_registry_test extends PHPUnit_Framework_TestCase {

	public function test_simple_set_and_get() {
        phpmachine_registry\set('foo', 'bar');
        $this->assertEquals('bar', phpmachine_registry\get('foo'));
    }

    public function test_is_set() {
        phpmachine_registry\set('baz', 'foo');
        $this->assertTrue(phpmachine_registry\exists('baz'));
        $this->assertFalse(phpmachine_registry\exists('test'));
    }

}
