<?php
require_once "PHPUnit/Autoload.php";
require_once (LIBPATH."SchemaBuilder.class.php");
class SchemaBuilderTest extends PHPUnit_Framework_TestCase {
	
	protected function setUp() {
		if (!is_dir(SCHEMAPATH)) {
			mkdir(SCHEMAPATH);	
		}
		
		// initialize schema builder
		SchemaBuilder::init();
		
	}
	
	/**
	 * @group Schemas
	 * 
	 * Tests if schemas are being actually being built
	 */	 
	public function testBuildSchema() {
		// generate schemas
		SchemaBuilder::generateSchemas('_8');
		$this->assertTrue(is_dir(SCHEMAPATH));
		
		$this->assertNotEmpty(glob(SCHEMAPATH.'*.schema.php'));
		
		// Test if patient schema was built
		$this->assertFileExists(SCHEMAPATH."patients.schema.php");
		// Test if appointment schema was built
		$this->assertFileExists(SCHEMAPATH."appointments.schema.php");
	}

	/**
	 * @depends testBuildSchema
	 * @group Schemas
	 * 
	 * Tests a random schema to make sure the $schema array exists
	 */
	public function testSchemaKeys() {
		// generate schemas
		SchemaBuilder::generateSchemas('common');
		SchemaBuilder::generateSchemas('_8');
		$this->assertTrue(is_dir(SCHEMAPATH));
				
		// get a list of all generated schemas
		$schema_files = glob(SCHEMAPATH.'*.schema.php');
		$this->assertTrue(is_array($schema_files));
		
		// pick a random schema
		$rand_schema = $schema_files[array_rand($schema_files)];		
		$this->assertFileExists($rand_schema);
		$this->assertTrue($rand_schema != '');
		
		// check that $schema exists
		require_once($rand_schema);
		// $schema should be inherited from the required file
		$this->assertTrue(is_array($schema));
		$this->assertNotEmpty($schema);
		$this->assertArrayHasKey('pri', $schema);
		$this->assertArrayHasKey('db_name', $schema);
		
	}

	protected function tearDown() {
	
	}

}
?>
