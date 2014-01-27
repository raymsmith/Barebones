<?php
namespace Barebones\Lib;
require_once(LIBPATH."CollectionModel.class.php");
abstract class ComplexModel extends Model{
	protected $base_model;
	protected $relationships = array();
	protected $bridges = array();
	protected $models = array();
	protected $config = array();
	public function __construct($id="",$config=""){
		if( is_array($config) ){
			$this->config = $config;
		}
		if( $this->validId($id) ){
			$this->load($id);
		}
	}
	protected function setBaseModel(Model $model){
		$this->base_model = $model;
	}
	public function getBaseModel(){
		return $this->base_model;
	}
	protected function addRelationship($relative){
		/* $relative['relative'] == column name of foreign key
		 * $relative['model'] == Model Name
		 */
		$this->relationships[$relative['name']] = $relative;
	}
	protected function addBridge($bridge){
		/* $relative['name'] == name of the bridge
		 * $relative['relative_key'] == column name of foreign key
		 * $relative['bridge'] == Model Name of Map/Bridge/Associative Table
		 * $relative['bridge_key'] == column name of foreign key
		 * $relative['bridge_model'] == Model Name
		 */
		$this->bridges[$bridge['name']] = $bridge;
	}
	public function getBridge($name){
		return $this->bridges[$name];
	}
	public function getRelationship($name){
		return $this->relationships[$name];
	}
	public function getModel($idx){
		return $this->models[$idx];
	}
	public function save(){
		//Coming Soon!
		$this->base_model->save();
		foreach($this->models as $model){
			$model->save();
		}
	}
	private function load($id){
		$model = get_class($this->base_model);
		$this->base_model = new $model($id);
		foreach($this->relationships as $idx => $relationship){
			if( isset($this->config['load']) && !in_array($relationship['name'],$this->config['load']) ){
				continue;
			}
			$r_model = $relationship['model'];
			$key = $this->base_model->$relationship['relative'];
			$this->models[] = new $r_model($key);
			if( !isset($this->relationships[$idx]['model_ids']) || !is_array($this->relationships[$idx]['model_ids']) )
				$this->relationships[$idx]['model_ids'] = array();
			$this->relationships[$idx]['model_id'] = ( count($this->models) - 1 );
		}
		foreach($this->bridges as $idx => $bridge_entry){
			if( isset($this->config['load']) && !in_array($bridge_entry['name'],$this->config['load']) )
				continue;

			// Build a collection of bridge models
			$relative_key = $bridge_entry['relative_key'];
			$bridge = $bridge_entry['bridge'];
			$bridge_key = $bridge_entry['bridge_key'];
			$bridge_model = $bridge_entry['bridge_model'];
			$args = array();
			$args['where'] = array(" `".$bridge_entry['relative_key']."` = '".$this->base_model->id."' ");
			$empty_bridge = new $bridge();
			$collection = new CollectionModel($empty_bridge,$args);
			// Post Process Data
			$data = $collection->getData();
			$this->bridges[$idx]['model_ids'] = array();
			foreach($data['data'] as $i => $entry){
				//For each collection row found create models
				$key = $entry[$bridge_key];
				$this->models[] = new $bridge($entry[$empty_bridge->pri]);
				$this->models[] = new $bridge_model($key);
				// die("count".count($this->models));
				$this->bridges[$idx]['model_ids'][] = array( 'model'=>(count($this->models) - 1),'map'=> ( count($this->models) - 2 ));
			}
		}
	}
}
?>
