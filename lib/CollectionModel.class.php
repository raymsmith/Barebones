<?php
namespace Barebones\Lib;
class CollectionModel{
	protected $model;
	protected $options;
	protected $total_rows;
	protected $sql;
	protected $data = array();
	const SQL_ONLY = 1;
	const DATA_ONLY = 2;
	const SQL_DATA = 3;
	const ALL_DATA = 4;
	public function __construct(Model $model,Array $options=NULL){
		$this->sql = array();
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
			$this->options['group_by'] = array();
			$this->options['having'] = "";
		}
		else{
			$this->options = $this->_validateOptions($options);
		}
		$this->_query();
	}
	public function getData($return=""){
        $results = array();
        if( $return == "" ){
            $results['data'] = $this->data;
            $results['total_rows'] = $this->total_rows;
        }
        else if($return == self::SQL_ONLY){
            $results = $this->sql;
        }
        else if($return == self::DATA_ONLY){
            $results = $this->data;
        }
        else if($return == self::SQL_DATA){
            $results['sql'] = $this->sql;
            $results['data'] = $this->data;
        }
        else if($return == self::ALL_DATA){
            $results['sql'] = $this->sql;
            $results['data'] = $this->data;
            $results['total_rows'] = $this->total_rows;
        }
        return $results;
    }

	public function toExcel($filename,$cols=""){
		if( !defined('ABSPATH') )
    		define('ABSPATH',BASEPATH."awa/");		
		$data = $this->data;
		$headers = array_keys($data[0]);
		if( $cols != "" && is_array($cols) && count($cols) == count($headers) )
			$headers = $cols;
		file_put_contents($filename, '"'.implode('"'."\t".'"',$headers).'"'."\r\n");
		foreach($data as $row){
			file_put_contents($filename, '"'.implode('"'."\t".'"',$row).'"'."\r\n",FILE_APPEND);
		}
		if( strtolower(substr(php_uname('n'),0,3)) != "dev" ){
			require_once(APIPATH."functions.cloudfiles.php");
			file_server_scp($filename);
		}
		return file_exists($filename);
	}
	private function _query(){
		if( is_a($this->model,'Barebones\\lib\\ComplexModel') ){
			$this->_queryComplexModel();
		}
		else{
			$this->_querySimpleModel();
		}
	}
	private function _querySimpleModel(){
		$info = $this->model->getSchemaInfo();
		$return_arr = $this->getReturnValues();
		$return_arr = current(explode(',', $return_arr));
		//if the retval has any column alias, strip it. Since we only use the first val anyway, we don't need to do it for all column/alias pairs
		if(strpos($return_arr, "as")){
			$return_arr = substr($return_arr, 0, strpos($return_arr, "as")-1);
		}
		$db = ApplicationDataConnectionPool::get($info['connection']);
		$sql = "SELECT SQL_CALC_FOUND_ROWS ".$this->getReturnValues()." FROM `".$info['db']."`.`".$info['table']."` ";
		if( isset($this->options['join']) && is_array($this->options['join']) && count($this->options['join']) > 0 )
			$sql .= implode(" ", $this->options['join']);
		if( isset($this->options['where']) && is_array($this->options['where']) && count($this->options['where']) > 0 )
			$sql .= " WHERE ".implode(" AND ",$this->options['where']);
		if(isset($this->options['group_by']) && count($this->options['group_by']) > 0 && is_array($this->options['group_by'])){
		    $sql .= " GROUP BY " . implode(",",$this->options['group_by']);
		}
		if(isset($this->options['having']) && (is_array($this->options['having']) || strlen($this->options['having']) > 0)){
			$sql .= " HAVING " . (is_array($this->options['having'])? implode(" AND ",$this->options['having']) : $this->options['having']);
		}

		if( isset($this->options['order_by']) && !empty($this->options['order_by']) )
			$sql .= " ORDER BY `".$this->options['order_by']."` ".( (isset($this->options['sort_by']) && !empty($this->options['sort_by'])) ? $this->options['sort_by'] : "ASC" );
		if( isset($this->options['limit']) && !empty($this->options['limit']) )
			$sql .= " LIMIT ".$this->options['limit'];
		if( isset($this->options['offset']) && trim($this->options['offset']) != "" )
			$sql .= " OFFSET ".$this->options['offset'];
		$sql = preg_replace('/\s+\s+/',' ',$sql)."\n";

		$this->sql = $sql;
		// echo $sql;
		$res = $db->query($sql);
		if( $db->get_error() != "" )
			throw new Exception(get_class($this)." Failed: ".$db->get_error()." ".$sql);
		$s2 = $db->query('select FOUND_ROWS()');
		if( $db->get_error() != "" )
			throw new Exception(get_class($this)." Failed: ".$db->get_error()." ".$sql);
		while($s2->hasNext()){
			$row = $s2->next();
			$this->total_rows = $row['FOUND_ROWS()'];
		}
		while($res->hasNext()){
			$row = $res->next();
			$this->data[] = $row;
		}

	}
	private function _queryComplexModel(){
		$info = $this->model->base_model->getSchemaInfo();
		$db = ApplicationDataConnectionPool::get($info['connection']);
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
		$this->sql['count'] = $count_sql;
		// return false;
		if( isset($this->options['where']) && is_array($this->options['where']) && count($this->options['where']) > 0 )
			$count_sql .= "WHERE ".implode(" AND ",$this->options['where']);
		if( isset($this->options['join']) && is_array($this->options['join']) && count($this->options['join']) > 0 )
			$count_sql .= implode(" ", $this->options['join']);
		if(isset($this->options['group_by']) && count($this->options['group_by']) > 0 && is_array($this->options['group_by'])){
		    $count_sql .= " GROUP BY " . implode(",",$this->options['group_by']);
		    if(isset($this->options['having']) && strlen($this->options['having']) > 0){
				$count_sql .= " HAVING " . $this->options['having'];
			}
		}
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
		if( isset($this->options['join']) && is_array($this->options['join']) && count($this->options['join']) > 0 )
			$sql .= implode(" ", $this->options['join']);
		if(isset($this->options['group_by']) && count($this->options['group_by']) > 0 && is_array($this->options['group_by'])){
		    $sql .= " GROUP BY " . implode(",",$this->options['group_by']);
		    if(isset($this->options['having']) && strlen($this->options['having']) > 0){
				$sql .= " HAVING " . $this->options['having'];
			}
		}
		if( isset($this->options['order_by']) && !empty($this->options['order_by']) )
			$sql .= " ORDER BY `".$this->options['order_by']."` ".( (isset($this->options['sort_by']) && !empty($this->options['sort_by'])) ? $this->options['sort_by'] : "ASC" );
		if( isset($this->options['limit']) && !empty($this->options['limit']) )
			$sql .= " LIMIT ".$this->options['limit'];
		if( isset($this->options['offset']) && trim($this->options['offset']) != "" )
			$sql .= " OFFSET ".$this->options['offset'];
		$sql = preg_replace('/\s+\s+/',' ',$sql)."\n";
		//echo $sql . "\n";
		$this->sql['sql'] = $sql;
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
				$ob .= $key." AS ".((strpos($val,"`") === false) ? "`".$val."`, ": $val).", ";
			}
		}
		$ob = substr($ob,0,-2)." ";
		// var_dump($ob);
		// die();
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
	
	//Builds options array for get/getrows/search
	//andrew - added to this class because i want to be able to use it in models.
	public static function buildCollectionOptions($args){
		//echo "===args===\n";
		//print_r($args);
		//echo "==Eargs===\n";
		$limit = $offset = $order_by = $sort_by = $dist = $params = "";
		if(isset($args['limit']) || isset($args['options']['limit']))
			$limit = (isset($args['options']['limit'])) ? $args['options']['limit'] : $args['limit'];
		if(isset($args['offset']) || isset($args['options']['offset']))
			$offset = (isset($args['options']['offset'])) ? $args['options']['offset'] : $args['offset'];
		if(isset($args['order_by']))
			$order_by = $args['order_by'];
		if(isset($args['sort_by']))
			$sort_by = $args['sort_by'];
		if(isset($args['params'])){
			$params = $args['params'];
		}
		$options = array(
			"limit"=>$limit,
			"offset"=>$offset,
			"order_by"=>$order_by,
			"sort_by"=>$sort_by,
			"params"=>$params
		);
		if(isset($args['return'])){
			$options['return'] = $args['return'];
		}

		if(isset($args['options'])){
			foreach($args['options'] as $key => $val){
				$options[$key] = $val;
			}
		}
		if(!isset($args['where'])){
			$args['where'] = array();
		}

		if(isset($args['filters'])){
			foreach($args['filters'] as $val){
				array_push($args['where'], $val);
			}
		}
		if(isset($args['where'])){
			if(!isset($options['where'])){
				$options['where'] = array();
			}
			foreach($args['where'] as $a){
				array_push($options['where'],$a);
			}
		}

		if(isset($args['join'])){
			$options['join'] = array();
			foreach($args['join'] as $a){
				array_push($options['join'],$a);
			}
		}

		if(isset($args['having'])){
			$options['having'] = array();
			foreach($args['having'] as $a){
				array_push($options['having'],$a);
			}
		}

		if(isset($args['params'])){
			$options['params'] = array();
			foreach($args['params'] as $a){
				array_push($options['params'], $a);
			}
		}

		if(isset($args['return'])){
			$options['return'] = $args['return'];
		}

		return $options;
	}
}
?>
