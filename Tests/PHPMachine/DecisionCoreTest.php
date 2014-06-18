<?php

namespace Tests\PHPMachine;

class DecisionCoreTest extends \PHPUnit_Framework_TestCase {

	public function testNotAvailable() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestResourceUnavailable', new Request(), new Response());
		$this->assertEquals(503, $response->status_code());
	}

	public function testExists() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestResourceExists', new Request(), new Response());
		$this->assertEquals(404, $response->status_code());
	}

	public function testAuthorized() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestAuthorized', new Request(), new Response());
		$this->assertEquals(401, $response->status_code());
	}

	public function testForbidden() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestForbidden', new Request(), new Response());
		$this->assertEquals(403, $response->status_code());
	}

	public function testMalformedRequest() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestMalformedRequest', new Request(), new Response());
		$this->assertEquals(400, $response->status_code());
	}

	public function testURITooLong() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestURITooLong', new Request(), new Response());
		$this->assertEquals(414, $response->status_code());
	}

	public function testKnownContentType() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestKnownContentType', new Request(), new Response());
		$this->assertEquals(415, $response->status_code());
	}

	public function testValidContentHeaders() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestValidContentHeaders', new Request(), new Response());
		$this->assertEquals(501, $response->status_code());
	}

	public function testValidEntityLength() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestValidEntityLength', new Request(), new Response());
		$this->assertEquals(413, $response->status_code());
	}

	public function testOptions() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestOptions', new Request('localhost', 80, 'OPTIONS'), new Response());
		$this->assertEquals(200, $response->status_code());
		$this->assertArrayHasKey('X-Testing123', $response->headers());
	}

	public function testAllowedMethods() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestResource', new Request('localhost', 80, 'WEIRD'), new Response());
		$this->assertEquals(405, $response->status_code());
	}

	public function testDeleteResource() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestDeleteResource', new Request('localhost', 80, 'DELETE'), new Response());
		$this->assertEquals(204, $response->status_code());
	}

	public function testDeleteResourceNoGuarentee() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestDeleteResourceNoGuarentee', new Request('localhost', 80, 'DELETE'), new Response());
		$this->assertEquals(202, $response->status_code());
	}

	public function testPostIsCreate() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestPostIsCreate', new Request('localhost', 80, 'POST'), new Response());
		$this->assertEquals(201, $response->status_code(), $response->body());
	}

	public function testPostIsCreateWithOutPath() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestPostIsCreateWithOutPath', new Request('localhost', 80, 'POST'), new Response());
		$this->assertEquals(500, $response->status_code(), $response->body());
	}

	public function testPost() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestPost', new Request('localhost', 80, 'POST'), new Response());
		$this->assertEquals(204, $response->status_code(), $response->body());
	}

	public function testContentTypesProvided() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestContentTypesProvided', new Request(), new Response());
		$this->assertEquals(200, $response->status_code(), $response->body());
		$this->assertEquals('I\'m cool!', $response->body());
	}

	public function testContentTypesProvidedFail() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestContentTypesProvided',
			new Request('localhost', 80, 'GET', 'http', '/', array(), array('Accept-Type'=>'text/html')),
			new Response());
		$this->assertEquals(406, $response->status_code(), $response->body());
	}

	public function testConflict() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestConflict', new Request('localhost', 80, 'POST'), new Response());
		$this->assertEquals(409, $response->status_code(), $response->body());
	}

	public function testMulitpleChoices() {
		$response = \PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestMulitpleChoices', new Request(), new Response());
		$this->assertEquals(300, $response->status_code(), $response->body());
	}

    // TODO test caching stuff

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

class TestPostIsCreate extends \PHPMachine\Resource {
	public static function postIsCreate(Request $request, array &$context){
		return true;
	}
	public static function resourceExists(Request $request, array &$context){
		return false;
	}
	public static function createPath(Request $request, array &$context) {
		return '/';
	}
}

class TestPostIsCreateWithOutPath extends \PHPMachine\Resource {
	public static function postIsCreate(Request $request, array &$context){
		return true;
	}
	public static function resourceExists(Request $request, array &$context){
		return false;
	}
}

class TestPost extends \PHPMachine\Resource {
	public static function processPost(Request $request, array &$context){
		return true;
	}
}

class TestContentTypesProvided extends \PHPMachine\Resource {
	public static function ContentTypesProvided(Request $request, array &$context){
		return array('text/plain'=>'toPlainText');
	}
	public static function toPlainText(Request $request, array &$context) {
		return 'I\'m cool!';
	}
}

class TestConflict extends \PHPMachine\Resource {
	public static function isConflict(Request $request, array &$context){
		return true;
	}
}

class TestMulitpleChoices extends \PHPMachine\Resource {
	public static function multipleChoices(Request $request, array &$context){
		return true;
	}
}
