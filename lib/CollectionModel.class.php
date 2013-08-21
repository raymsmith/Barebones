<?php
use BarebonesPHP as Barebones;
class CollectionModel{
	protected $model;
	protected $options;
	protected $total_rows;
	protected $data = array();
	public function __construct(Model $model,Array $options=NULL){
		$this->data = array();
		$this->model = $model;
		$this->options = $options;
		$this->data = array();
		if( !is_array($this->options) || is_null($this->options) ){
			$this->options['limit'] = 25;
			$this->options['offset'] = 0;
			$this->options['order_by'] = "";
			$this->options['sort_by'] = "";
			$this->options['return'] = array();
			$this->options['where'] = array();
		}
		else{
			$this->options = $this->_validateOptions($options);
		}
		$this->_query();
	}
	public function getData(){
		$results = array();
		$results['data'] = $this->data;
		$results['total_rows'] = $this->total_rows;
		return $results;
	}

	private function _query(){
		if( is_a($this->model,'ComplexModel') ){
			$this->_queryComplexModel();
		}
		else{
			$this->_querySimpleModel();
		}
	}
	private function _querySimpleModel(){
		$info = $this->model->getSchemaInfo();
		$db = Barebones\ApplicationDataConnectionPool::get($info['connection']);
		$count_sql = "SELECT COUNT(*) as `num_rows` FROM `".$info['db']."`.`".$info['table']."` ";
		if( isset($this->options['join']) && is_array($this->options['join']) && count($this->options['join']) > 0 )
			$count_sql .= implode(" ", $this->options['join']);
		if( isset($this->options['where']) && is_array($this->options['where']) && count($this->options['where']) > 0 )
			$count_sql .= " WHERE ".implode(" AND ",$this->options['where']);
		$res = $db->query($count_sql);
		if( $db->get_error() != "" )
			throw new Exception(get_class($this)." Num Rows Failed: ".$db->get_error()." ".$count_sql);
		while($res->hasNext()){
			$row = $res->next();
			$this->total_rows = $row['num_rows'];
		}
		// echo $count_sql;

		$sql = "SELECT ".$this->getReturnValues()." FROM `".$info['db']."`.`".$info['table']."` ";
		if( isset($this->options['join']) && is_array($this->options['join']) && count($this->options['join']) > 0 )
			$sql .= implode(" ", $this->options['join']);
		if( isset($this->options['where']) && is_array($this->options['where']) && count($this->options['where']) > 0 )
			$sql .= " WHERE ".implode(" AND ",$this->options['where']);
		if( isset($this->options['order_by']) && !empty($this->options['order_by']) )
			$sql .= " ORDER BY `".$this->options['order_by']."` ".( (isset($this->options['sort_by']) && !empty($this->options['sort_by'])) ? $this->options['sort_by'] : "ASC" );
		if( isset($this->options['limit']) && !empty($this->options['limit']) )
			$sql .= " LIMIT ".$this->options['limit'];
		if( isset($this->options['offset']) && trim($this->options['offset']) != "" )
			$sql .= " OFFSET ".$this->options['offset'];
		$sql = preg_replace('/\s+\s+/',' ',$sql)."\n";
		// echo $sql . "\n";
		$res = $db->query($sql);
		if( $db->get_error() != "" )
			throw new Exception(get_class($this)." Failed: ".$db->get_error()." ".$sql);
		while($res->hasNext()){
			$row = $res->next();
			$this->data[] = $row;
		}		

	}
	private function _queryComplexModel(){
		$info = $this->model->base_model->getSchemaInfo();
		$db = Barebones\ApplicationDataConnectionPool::get($info['connection']);
		$count_sql = "SELECT COUNT(*) as `num_rows` FROM `".$info['db']."`.`".$info['table']."` ";
		$join_sql = "";
		foreach($this->model->relationships as $relationship){
			$temp_model = $relationship['model'];
			$temp_model = new $temp_model();
			$model_info = $temp_model->getSchemaInfo();
			$join_sql .= "LEFT JOIN `".$model_info['db']."`.`".$model_info['table']."` as `".$relationship['name']."` ON `".$model_info['db']."`.`".$relationship['name']."`.`".$temp_model->pri."` = `".$info['db']."`.`".$info['table']."`.`".$relationship['relative']."` ";
		}
		foreach ($this->model->bridges as $bridge_entry) {
			/* $relative['name'] == name of the bridge
			 * $relative['relative_key'] == column name of foreign key
			 * $relative['bridge'] == Model Name of Map/Bridge/Associative Table
			 * $relative['bridge_key'] == column name of foreign key
			 * $relative['bridge_model'] == Model Name
			 */			
			$temp_name = $bridge_entry['name'];
			$temp_key = $bridge_entry['relative_key'];
			$temp_bridge = $bridge_entry['bridge'];
			$temp_bridge_key = $bridge_entry['bridge_key'];
			$temp_bridge_model = $bridge_entry['bridge_model'];
			$bridge = new $temp_bridge();
			$bridge_model = new $temp_bridge_model();
			$bridge_info = $bridge->getSchemaInfo();
			$bridge_model_info = $bridge_model->getSchemaInfo();
			$join_sql .= "LEFT JOIN `".$bridge_info['db']."`.`".$bridge_info['table']."` as `".$bridge_entry['name']."Bridge` ON `".$bridge_info['db']."`.`".$bridge_entry['name']."Bridge`.`".$bridge_entry['relative_key']."` = `".$info['db']."`.`".$info['table']."`.`".$this->model->base_model->pri."` ";
			$join_sql .= "LEFT JOIN `".$bridge_model_info['db']."`.`".$bridge_model_info['table']."` as `".$bridge_entry['name']."` ON `".$bridge_model_info['db']."`.`".$bridge_entry['name']."`.`".$bridge_model->pri."` = `".$bridge_model_info['db']."`.`".$bridge_entry['name']."Bridge`.`".$bridge_entry['bridge_key']."` ";
		}
		$count_sql .= $join_sql;
		// $this->data[] = $count_sql;
		// return false;
		if( isset($this->options['where']) && is_array($this->options['where']) && count($this->options['where']) > 0 )
			$count_sql .= "WHERE ".implode(" AND ",$this->options['where']);		
		$res = $db->query($count_sql);
		if( $db->get_error() != "" )
			throw new Exception(get_class($this)." Num Rows Failed: ".$db->get_error()." ".$count_sql);
		while($res->hasNext()){
			$row = $res->next();
			$this->total_rows = $row['num_rows'];
		}

		$sql = "SELECT ".$this->getReturnValues()." FROM `".$info['db']."`.`".$info['table']."` ";
		$sql .= $join_sql;
		if( isset($this->options['where']) && is_array($this->options['where']) && count($this->options['where']) > 0 )
			$sql .= "WHERE ".implode(" AND ",$this->options['where']);
		if( isset($this->options['order_by']) && !empty($this->options['order_by']) )
			$sql .= " ORDER BY `".$this->options['order_by']."` ".( (isset($this->options['sort_by']) && !empty($this->options['sort_by'])) ? $this->options['sort_by'] : "ASC" );
		if( isset($this->options['limit']) && !empty($this->options['limit']) )
			$sql .= " LIMIT ".$this->options['limit'];
		if( isset($this->options['offset']) && trim($this->options['offset']) != "" )
			$sql .= " OFFSET ".$this->options['offset'];
		$sql = preg_replace('/\s+\s+/',' ',$sql)."\n";
		//echo $sql . "\n";
		$res = $db->query($sql);
		if( $db->get_error() != "" )
			throw new Exception(get_class($this)." Failed: ".$db->get_error()." ".$sql);
		while($res->hasNext()){
			$row = $res->next();
			$this->data[] = $row;
		}
	}
	private function getReturnValues(){
		$ob = "";
		if( !isset($this->options['return']) || count($this->options['return']) == 0 )
			return "*";
		foreach($this->options['return'] as $key => $val){
			if( is_numeric($key) )
				$ob .=  (strpos($val,"`") === false) ? "`".$val."`, ": $val.", " ;
			else{
				$key = ( strpos($key,"`") !== false )?$key:"`".$key."`";
				$ob .= $key." AS `".$val."`, ";
			}
		}
		$ob = substr($ob,0,-2)." ";
		return $ob;
	}
	private function _validateOptions($options){
		if( !isset($this->options['limit']) || empty($this->options['limit']) ){
			$options['limit'] = "";
			$options['offset'] = "";
		}
		if( isset($this->options['return']) && !is_array($this->options['return']) )
			throw new Exception(get_class($this)." Invalid Option: return must be an array");
		if( isset($this->options['where']) && !is_array($this->options['where']) )
			throw new Exception(get_class($this)." Invalid Option: where must be an array");
		return $options;
	}
}
?>
