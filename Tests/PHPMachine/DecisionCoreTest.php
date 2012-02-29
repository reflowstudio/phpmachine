<?php

namespace Tests\PHPMachine;

class DecisionCoreTest extends \PHPUnit_Framework_TestCase {

	public function testNotAvailable() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestResourceUnavailable', new Request(), new Response());
		$this->assertEquals(503, $response->getStatusCode());
    }

	public function testExists() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestResourceExists', new Request(), new Response());
		$this->assertEquals(404, $response->getStatusCode());
    }

	public function testAuthorized() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestAuthorized', new Request(), new Response());
		$this->assertEquals(401, $response->getStatusCode());
    }

	public function testForbidden() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestForbidden', new Request(), new Response());
		$this->assertEquals(403, $response->getStatusCode());
    }

	public function testMalformedRequest() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestMalformedRequest', new Request(), new Response());
		$this->assertEquals(400, $response->getStatusCode());
    }

	public function testURITooLong() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestURITooLong', new Request(), new Response());
		$this->assertEquals(414, $response->getStatusCode());
    }

	public function testKnownContentType() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestKnownContentType', new Request(), new Response());
		$this->assertEquals(415, $response->getStatusCode());
    }

	public function testValidContentHeaders() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestValidContentHeaders', new Request(), new Response());
		$this->assertEquals(501, $response->getStatusCode());
    }

	public function testValidEntityLength() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestValidEntityLength', new Request(), new Response());
		$this->assertEquals(413, $response->getStatusCode());
    }

	public function testOptions() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestOptions', new Request('localhost', 80, 'OPTIONS'), new Response());
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertArrayHasKey('X-Testing123', $response->getHeaders());
    }

	public function testAllowedMethods() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestResource', new Request('localhost', 80, 'WEIRD'), new Response());
		$this->assertEquals(405, $response->getStatusCode());
    }

	public function testDeleteResource() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestDeleteResource', new Request('localhost', 80, 'DELETE'), new Response());
		$this->assertEquals(204, $response->getStatusCode());
    }

	public function testDeleteResourceNoGuarentee() {
		$response = \PHPMachine\DecisionCore::handleRequest('\Tests\\PHPMachine\\TestDeleteResourceNoGuarentee', new Request('localhost', 80, 'DELETE'), new Response());
		$this->assertEquals(202, $response->getStatusCode());
    }

}

class TestResourceUnavailable extends \PHPMachine\Resource {
	public static function serviceAvailable(Request $request, array &$context){
		return false;
	}
}

class TestResourceExists extends \PHPMachine\Resource {
	public static function resourceExists(Request $request, array &$context){
		return false;
	}
}

class TestAuthorized extends \PHPMachine\Resource {
	public static function isAuthorized(Request $request, array &$context){
		return false;
	}
}

class TestForbidden extends \PHPMachine\Resource {
	public static function forbidden(Request $request, array &$context){
		return true;
	}
}

class TestMalformedRequest extends \PHPMachine\Resource {
	public static function malformedRequest(Request $request, array &$context){
		return true;
	}
}

class TestURITooLong extends \PHPMachine\Resource {
	public static function uriTooLong(Request $request, array &$context){
		return true;
	}
}

class TestKnownContentType extends \PHPMachine\Resource {
	public static function knownContentType(Request $request, array &$context){
		return false;
	}
}

class TestValidContentHeaders extends \PHPMachine\Resource {
	public static function validContentHeaders(Request $request, array &$context){
		return false;
	}
}

class TestValidEntityLength extends \PHPMachine\Resource {
	public static function validEntityLength(Request $request, array &$context){
		return false;
	}
}

class TestOptions extends \PHPMachine\Resource {
	public static function options(Request $request, array &$context){
		return array('X-Testing123'=>'I\'m cool!');
	}
}

class TestDeleteResource extends \PHPMachine\Resource {
	public static function deleteResource(Request $request, array &$context){
		return true;
	}
}

class TestDeleteResourceNoGuarentee extends \PHPMachine\Resource {

	public static function deleteResource(Request $request, array &$context){
		return true;
	}

	public static function deleteCompleted(Request $request, array &$context){
		return false;
	}
}

