<?php
namespace BarebonesPHP;
abstract class Schema {
	protected $db;
	protected $table;
	protected $pri;
	protected $schema;
	protected $delete_method;
	protected function loadSchema() {
		if (file_exists(SCHEMAPATH.$this->table.".schema.php")) {
			require (SCHEMAPATH.$this->table.".schema.php");
			$this->schema = $schema;
			$this->pri = $this->schema['pri'];
			$this->delete_method = $this->schema['delete_method'];
		} else {
			throw new Exception(get_class($this)." Failed to load schema: File does not exist ".SCHEMAPATH.$this->table.".schema.php");
		}
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