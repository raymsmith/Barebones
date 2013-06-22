<?php
require_once "PHPUnit/Autoload.php";
require_once (LIBPATH."SchemaBuilder.class.php");
class ModelTest extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		if (!is_dir(SCHEMAPATH)) {
			mkdir(SCHEMAPATH);
		}
		// initialize schema builder
		SchemaBuilder::init();

		$_SESSION['usr_cur_customer_id'] = "_8";
	}

	/**
	 * @group Models
	 *
	 * Tests a customer model
	 */
	public function testCustomerModel() {
		// generate schemas
		SchemaBuilder::generateSchemas('_8');
		$this->assertTrue(is_dir(MODELPATH));
		$this->assertNotEmpty(glob(MODELPATH.'*.class.php'));
		$this->assertTrue(is_file(MODELPATH.'AppointmentModel.class.php'));
		require_once (MODELPATH.'AppointmentModel.class.php');
		$model = new AppointmentModel();
		$this->assertNotEmpty($model);
		// run model tests
		$this->generalModelTests($model);
		$this->stubbedModelTests($model);
	}

	/**
	 * @group Models
	 *
	 * Tests a common model
	 */
	public function testCommonModel() {
		// generate schemas
		SchemaBuilder::generateSchemas('common');
		$this->assertTrue(is_dir(MODELPATH));
		$this->assertNotEmpty(glob(MODELPATH.'*.class.php'));
		$this->assertTrue(is_file(MODELPATH.'ZipmapModel.class.php'));
		require_once (MODELPATH.'ZipmapModel.class.php');
		$model = new ZipmapModel();
		$this->assertNotEmpty($model);
		// run model tests
		$this->generalModelTests($model);
		$this->stubbedModelTests($model);
	}

	/**
	 * @group Models
	 *
	 * Tests a customer model
	 */
	public function testLoadCustomerModel() {
		// generate schemas
		SchemaBuilder::generateSchemas('_8');
		$this->assertTrue(is_dir(MODELPATH));
		$this->assertNotEmpty(glob(MODELPATH.'*.class.php'));
		$this->assertTrue(is_file(MODELPATH.'AppointmentModel.class.php'));
		require_once (MODELPATH.'AppointmentModel.class.php');
		$model = new AppointmentModel(104811);
		$this->assertNotEmpty($model);
		// run model tests
		$this->generalModelTests($model);
		$this->loadedModelTests($model);
	}

	/**
	 * Defines a series of general tests applicable to all models
	 */
	protected function generalModelTests($model) {
		$this->assertObjectHasAttribute('connection', $model);
		$this->assertObjectHasAttribute('database', $model);
		$this->assertObjectHasAttribute('db', $model);
		$this->assertObjectHasAttribute('attributes', $model);
		$this->assertObjectHasAttribute('primary_key', $model);

		// schema information
		$this->assertTrue(is_array($model->getSchemaInfo()));
		$this->assertNotEmpty($model->getSchemaInfo());
		// attributes
		$this->assertTrue(is_array($model->getAttributes()));
		$this->assertNotEmpty($model->getAttributes());
	}

	/**
	 * Defines a series of tests for stubbed(empty) models
	 */
	protected function stubbedModelTests($model) {
		// pick a random key out of the attributes
		$key = array_rand($model->getAttributes());
		// make sure key is empty
		$this->assertTrue($model->$key == "");
		// test set/get
		$model->$key = "foobar";
		$this->assertEquals($model->$key, "foobar");
	}

	/**
	 * Defines a series of tests for models loaded from the database
	 */
	protected function loadedModelTests($model) {
		// generate a random number from 1 to size of model attributes
		$count = mt_rand(1, count($model->getAttributes()) - 1);
		$num_changed = $count;
		$attrs = array();
		// change a random number of model attributes
		while ($count > 0) {
			$key = array_rand($model->getAttributes());
			
			if ($key != $model->pri) {
				if (array_key_exists($key, $attrs)) {
					continue;
				} else {
					// test set/get
					$model->$key = "foobar";
					$this->assertEquals($model->$key, "foobar");
					// add key to array so we don't change it twice
					$attrs[$key] = $model->$key;
					$count--;
				}
			}
		}
		// verify the whatchanged() function kept track of all changes
		$this->assertCount($num_changed, $model->whatChanged(), "whatChanged() failed to keep track of number of changes");
		// update
		$model->save();
	}

}
?>