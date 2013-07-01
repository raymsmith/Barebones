<?php
require_once "PHPUnit/Autoload.php";
require_once(CONTROLLERSPATH."common_controller.class.php");
class ResponseTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Response::displayHeaders(false);
    }
    public function test200(){
		// Setup
		Response::setBody(array("success"=>"1"));
		Response::send_200();
		$body = Response::getBody();
		$header = Response::getHeaders();
		// Test Body Structure
		$this->assertTrue(is_string($body));
		$body = json_decode($body,true);
		$this->assertTrue(is_array($body));
		// Test Success
		$this->assertArrayHasKey('success',$body);
		$this->assertEquals(1,$body['success']);
		// Test Header Structure
		$this->assertTrue(is_array($header));
		$this->assertArrayHasKey('Response-Code',$header);
		$this->assertTrue(is_array($header['Response-Code']));
		$this->assertEquals(200,$header['Response-Code'][2]);
	}
	public function test400(){
		// Setup
		Response::setBody(array("success"=>"0"));
		Response::send_400();
		$body = Response::getBody();
		$header = Response::getHeaders();
		// Test Body Structure
		$this->assertTrue(is_string($body));
		$body = json_decode($body,true);
		$this->assertTrue(is_array($body));
		// Test Success
		$this->assertArrayHasKey('success',$body);
		$this->assertEquals(0,$body['success']);
		// Test Header Structure
		$this->assertTrue(is_array($header));
		$this->assertArrayHasKey('Response-Code',$header);
		$this->assertTrue(is_array($header['Response-Code']));
		$this->assertEquals(400,$header['Response-Code'][2]);
	}
	public function test403(){
		// Setup
		Response::setBody(array("success"=>"0"));
		Response::send_403();
		$body = Response::getBody();
		$header = Response::getHeaders();
		// Test Body Structure
		$this->assertTrue(is_string($body));
		$body = json_decode($body,true);
		$this->assertTrue(is_array($body));
		// Test Success
		$this->assertArrayHasKey('success',$body);
		$this->assertEquals(0,$body['success']);
		// Test Header Structure
		$this->assertTrue(is_array($header));
		$this->assertArrayHasKey('Response-Code',$header);
		$this->assertTrue(is_array($header['Response-Code']));
		$this->assertEquals(403,$header['Response-Code'][2]);
	}
	public function test404(){
		// Setup
		Response::setBody(array("success"=>"0"));
		Response::send_404();
		$body = Response::getBody();
		$header = Response::getHeaders();
		// Test Body Structure
		$this->assertTrue(is_string($body));
		$body = json_decode($body,true);
		$this->assertTrue(is_array($body));
		// Test Success
		$this->assertArrayHasKey('success',$body);
		$this->assertEquals(0,$body['success']);
		// Test Header Structure
		$this->assertTrue(is_array($header));
		$this->assertArrayHasKey('Response-Code',$header);
		$this->assertTrue(is_array($header['Response-Code']));
		$this->assertEquals(404,$header['Response-Code'][2]);
	}
	public function test405(){
		// Setup
		Response::setBody(array("success"=>"0"));
		Response::send_405();
		$body = Response::getBody();
		$header = Response::getHeaders();
		// Test Body Structure
		$this->assertTrue(is_string($body));
		$body = json_decode($body,true);
		$this->assertTrue(is_array($body));
		// Test Success
		$this->assertArrayHasKey('success',$body);
		$this->assertEquals(0,$body['success']);
		// Test Header Structure
		$this->assertTrue(is_array($header));
		$this->assertArrayHasKey('Response-Code',$header);
		$this->assertTrue(is_array($header['Response-Code']));
		$this->assertEquals(405,$header['Response-Code'][2]);
	}
	public function test500(){
		// Setup
		Response::setBody(array("success"=>"0"));
		Response::send_500();
		$body = Response::getBody();
		$header = Response::getHeaders();
		// Test Body Structure
		$this->assertTrue(is_string($body));
		$body = json_decode($body,true);
		$this->assertTrue(is_array($body));
		// Test Success
		$this->assertArrayHasKey('success',$body);
		$this->assertEquals(0,$body['success']);
		// Test Header Structure
		$this->assertTrue(is_array($header));
		$this->assertArrayHasKey('Response-Code',$header);
		$this->assertTrue(is_array($header['Response-Code']));
		$this->assertEquals(500,$header['Response-Code'][2]);
	}
    protected function tearDown()
    {
        unset($this->controller);
    }
}
?>
