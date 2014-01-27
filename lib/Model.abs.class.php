<?php
namespace Barebones\Lib;
abstract class Model extends Schema{
	protected $connection;
	protected $database;
	protected $db;
	protected $attributes = array();
	protected $primary_key;
	protected $id;
	protected $rules;
	protected $loaded;
	protected $class_methods = array();
	protected $table;
	protected $blacklistKeys = array();
    public function __construct($connection,$table,$id=""){
        $this->connection = $connection;
        $this->db = $connection;
        $this->table = $table;
        $this->attributes = array();
        $this->name_space = $this->db;
        $this->loadSchema();
		$this->id = $id;
		$this->rules = array();
		$this->class_methods = get_class_methods($this);
        if( $this->validId($id) ){
            $this->load($id);
        }
        else
            $this->stub();
	}

	public function timestampCreate(){
		$timestamp = date('Y-m-d H:i:s');
		return $timestamp;
	}

	public function timestampValidate($stamp){
		$pattern = '/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/';
		if(preg_match($pattern, $stamp)){
			return true;
		}else{
			return false;
		}
	}

	private function _getValue($col, $val){
		$type = $this->schema[$col]['attributes']['type'];
		$retVal;
		if($type == "int" || $type == "tinyint"){
			$retVal = (int)$val;
		}else{
			$retVal = (string)$val;
		}
		switch($type){
			case "int":
				$retVal = (int)$val;
				break;
			case "tinyint":
				$retVal = (int)$val;
				break;
			default:
				$retVal = (string)$val;
				break;
		}
		return $retVal;
	}

	private function load($id){
		$db = ApplicationDataConnectionPool::get($this->connection);
		$sql = "SELECT * FROM `".$this->db."`.`".$this->table."` WHERE `".$this->pri."` = '".$id."' LIMIT 1;";
		$res = $db->query($sql);
		if( $db->get_error() != "" )
			throw new Exception(get_class($this)." Failed to load: ".$db->get_error()." ".$sql);
		if( $res->num_rows() == 0 )
			throw new Exception(get_class($this)." Failed to load: Row not found");

		while($res->hasNext()){
			$row = $res->next();
			foreach($row as $col => $val){
				if( $col == "pri")
					$this->primary_key = array($val,0);

				$this->attributes[$col] = array($this->_getValue($col,$val),0);
			}
		}
		$this->loaded = true;
	}
	private function stub(){
		foreach($this->schema as $col => $val){
			if( $col != "db_name" && $col != "pri" ){
				$this->attributes[$col] = array("",0);
			}
		}
	}
	public function getSchemaInfo(){
		$retval = array();
		$retval['connection'] = $this->connection;
		$retval['db'] = $this->db;
		$retval['table'] = $this->table;

		return $retval;
	}
	public function getAttributes(){
		$attr = array();
		foreach($this->attributes as $key => $arr)
		{
			$attr[$key] = $arr[0];
		}
		return $attr;
	}
	public function save(){
        try{
		if( $this->_validate() ){
			if($this->validId($this->id)){
				$this->update();
			} else {
				$this->insert();
			}
		}
        } catch(Exception $e){
            throw new Exception($e->getMessage());
        }
	}

	public function getColumns(){
		return $this->attributeKeys;
	}

	private function update(){
		if( !$this->loaded )
			throw new Exception(get_class($this)." Failed to update: Model not Loaded");
		$db = ApplicationDataConnectionPool::get($this->connection);
		$update = array();
		foreach($this->attributes as $key => $arr)
		{
			if( $arr[1] == 1 )
				$update[$key] = $arr[0];
		}
		if( count($update) > 0 )
		{
			$sql = "UPDATE `".$this->db."`.`".$this->table."` SET ";
			foreach( $update as $key => $val )
			{
				$sql .= "`".$key."` = '".$db->escape($val)."', ";
			}
			$sql = substr($sql,0,-2)." WHERE `".$this->pri."` = '".$this->id."' LIMIT 1;";
			$res = $db->query($sql);
			if( $db->get_error() != "" )
				throw new Exception(get_class($this)." Failed to update: ".$db->get_error()."".$sql);
		}
	}
	private function insert(){
		$db = ApplicationDataConnectionPool::get($this->connection);
        $changes = $this->whatChanged();
        if( isset($changes[$this->pri]) )
            unset($changes[$this->pri]);
		if( count($changes) > 0 )
		{
			$cols = array();
			$vals = array();
			foreach( $changes as $key => $val )
			{
				$cols[] = $key;
				$vals[] = $db->escape($val);
			}
			$sql = "INSERT INTO `".$this->db."`.`".$this->table."` (`".implode('`,`',$cols)."`) VALUES ('".implode("','",$vals)."');";
			$res = $db->query($sql);
			if( $db->get_error() != "" )
				throw new Exception(get_class($this)." Failed to insert: ".$db->get_error()."".$sql);
			$select_back = "SELECT `".$this->pri."` FROM `".$this->db."`.`".$this->table."` WHERE ";
			foreach( $changes as $key => $val )
			{
					$select_back .= "`".$key."` = '".$db->escape($val)."' AND ";
			}
			$select_back = substr($select_back,0,-4)." ORDER BY `".$this->pri."` DESC LIMIT 1;";
			$res = $db->query($select_back);
			if( $db->get_error() != "" )
				throw new Exception(get_class($this)." Failed to select back: ".$db->get_error()."".$select_back);
			if($res->hasNext()){
					$row = $res->next();
					$this->attributes[$this->pri] = array($this->_getValue($this->pri,$row[$this->pri]),0);
                    $this->id = $row[$this->pri];
			} else {
				throw new Exception(get_class($this)." Failed to select back: Submitted changes did not much what was recorded into the database. Be sure that what you've input has not been altered by database rules.");
			}
			$this->loaded = true;
		}
	}
	public function delete(){
		if( !$this->loaded )
			throw new Exception(get_class($this)." Failed to update: Model not Loaded");
		if( $this->delete_method != "DELETE"){
			$disabled_col = $this->delete_method;
			$this->$disabled_col = 1;
			$this->update();
		}
		else{
			$db = ApplicationDataConnectionPool::get($this->connection);
			$sql = "DELETE FROM `".$this->db."`.`".$this->table."`  WHERE `".$this->pri."` = '".$this->id."' LIMIT 1;";
			$res = $db->query($sql);
			if( $db->get_error() != "" )
				throw new Exception(get_class($this)." Failed to update: ".$db->get_error()."".$sql);
		}
	}
	public function setRule($field,$rules){
		/*
		 * Rules definition
		 * @param String $field  The Column Name
		 * @param Array  $rules  Array of conditions
		 * conditions are function names
		 * default php functions can be found here http://www.php.net/manual/en/function.is-int.php
		 */
		 if( isset($this->rules[$field]) )
			$this->rules[$field] = array_merge_recursive($this->rules[$field],$rules);
		 else
			$this->rules[$field] = $rules;
	}

	/**
	 * Allows for a list of blacklisted keys that are immutable
	 * @param Array $keys List of keys to be blacklisted
	 */
	public function setBlacklistKeys($keys){
		foreach($keys as $key){
			$this->blacklistKeys[] = $key;
		}
	}
	/**
	 * Allows for verifying that the key passed in is a valid attribute key of the model
	 * @param  String  $key Attribute name
	 * @return Boolean  true of false depending on evalutated result
	 */
	public function isModelAttribute($key){
		return in_array($key, $this->attributeKeys);
	}
	private function _validate(){
		foreach($this->attributes as $key => $arr){
			$field = $key;
			// If there are no validation rules for this field, continue
			if( !isset($this->rules[$field]) )
				continue;
			$rules = $this->rules[$field];
			foreach($rules as $rule){
				// Adding a ? before a function name tell the model only to validate if the field has a value.
				if( substr($rule,0,1) == "?" ){
					if( empty($this->$field) ){
						continue;
					}
					else{
						$rule = substr($rule,1);
					}
				}
				// if( in_array($rule, $this->class_methods) ){
				if( method_exists($this,$rule) ){
					if( !$this->$rule($this->$field) ){
						throw new Exception(get_class($this)." Failed to validate user defined rule: ".$rule." is not true for ".$key." = ".$this->$field.";" );
					}
				}
				else if( function_exists($rule) ){
					if( !$rule($this->$field) ){
						print_r($this->class_methods);
						throw new Exception(get_class($this)." Failed to validate: ".$rule." is not true for ".$key. " = " . $this->$field.";" );
					}
				}
				else{
					throw new Exception(get_class($this)." Failed to validate: Unknown validation function ".$rule."!" );
				}
			}
		}
		return true;
	}
	public function whatChanged(){
		$changed = array();
		foreach($this->attributes as $key => $arr)
		{
			if( $arr[1] == 1 )
				$changed[$key] = $arr[0];
		}
		return $changed;
	}
	private function isLoaded(){
		return $this->loaded;
	}
	public function __get($key){
		if(array_key_exists($key,  $this->attributes))
			return $this->attributes[$key][0];
		else
			return $this->$key;
	}
	public function __set($key,$val){
		if(in_array($key, $this->blacklistKeys)){
			throw new Exception("Attempting to set the value of a blacklisted key");
		}else if($key != $this->primary_key){
			$this->attributes[$key] = array($val,1);
		}
	}
	/**
	 * [set Pass an array of attributes to set in the model. Filters through and finds permissable attributes]
	 * @param  [Array] $attr [description]
	 * @return [Array] $options [description]
	 */
	public function set($attr){
		$allowed_attr = $this->attributeKeys;
		foreach($attr as $col => $val){
			if(in_array($col, $allowed_attr)){
				$this->__set($col, $val);
			}
		}
	}
	public function __isset($key){
		return isset($this->attributes[$key]);
	}
	// Common Validation functions
	protected function is_boolean($val){
		if( $val === '0' || $val === '1' || $val === 0 || $val === 1 || $val === true || $val === false ){
			return true;
		}
		return false;
	}
	protected function is_char($val){
		return preg_match('/^[a-zA-Z]{1}$/',$val);
	}
	protected function is_integer($val){
		// Warning this only matched positive intergers
		// Don't do this.
		// return true;
		return preg_match('/^(-|){0,1}[0-9]{1,}$/',$val);
	}
	protected function is_required($val){
		return !empty($val);
	}
	protected function is_currency($val){
		return preg_match('/^\d+\.\d\d$/',$val);;
	}
	protected function isDatabaseDate($value){
		return ( preg_match('/^[0-9]{8}$/', $value) && checkdate(substr($value,4,2), substr($value,6,2), substr($value,0,4)) );
	}
}
?>
