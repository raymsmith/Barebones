<?php
class CollectionModel{
	protected $model;
	protected $options;
	protected $total_rows;
	protected $data;
	public function __construct(Model $model,Array $options=NULL){
		$this->model = $model;
		$this->options = $options;
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
		$info = $this->model->getSchemaInfo();
		$db = ApplicationDataConnectionPool::get($info['connection']);
		$count_sql = "SELECT COUNT(*) as `num_rows` FROM `".$info['db']."`.`".$info['table']."` ";
		if( isset($this->options['join']) && is_array($this->options['join']) && count($this->options['join']) > 0 )
			$count_sql .= implode(" ", $this->options['join']);
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
		if( isset($this->options['join']) && is_array($this->options['join']) && count($this->options['join']) > 0 )
			$sql .= implode(" ", $this->options['join']);
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
				$ob .=  (strpos($val,"`") === false) ? "`".$val."`, ": $val." " ;
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
