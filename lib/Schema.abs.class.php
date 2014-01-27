<?php
namespace Barebones\Lib;
abstract class Schema {
	protected $db;
	protected $name_space;
	protected $table;
	protected $pri;
	protected $attributeKeys;
	protected $schema;
	protected $delete_method;
	protected function loadSchema() {
		if (file_exists(SCHEMAPATH.$this->name_space.".".$this->table.".schema.php")) {
			$this->_loadSchemaFile();
		} else {
			SchemaBuilder::init();
			SchemaBuilder::generateSchemas(($this->name_space == "customer")? "_8" : $this->name_space, $this->table);
			$this->_loadSchemaFile();
		}
	}

	protected function _loadSchemaFile() {
		require (SCHEMAPATH.$this->name_space.".".$this->table.".schema.php");
		$this->schema = $schema;
		$this->attributeKeys = array_keys(array_diff_key($schema, array_flip(array('delete_method', 'pri', 'db_name'))));
		$this->pri = $this->schema['pri'];
		$this->delete_method = $this->schema['delete_method'];
	}

	protected function validId($id) {
		if (!empty($id) && is_numeric($id) && $id > 0)
			return true;
		else
			return false;
	}

	protected function validateSchema() {
		// TODO
	}

}
?>