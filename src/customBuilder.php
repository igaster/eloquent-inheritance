<?php namespace igaster\EloquentInheritance;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;

class customBuilder {
	protected $query;
	protected $model;
	
	public function __construct(InheritsEloquent $model, Builder $query){
		$this->query = $query;
		$this->model = $model;
	}

	// -----------------------------------------------
	// Testing Helper Methods
	// -----------------------------------------------

	public function testEcho($value){
		return $value;
	}

	// -----------------------------------------------
	// Break / Compose models
	// -----------------------------------------------

	public function createModelsFromQuery($data = []){
		$parent = $this->getParentTable();
		$child = $this->getChildTable();
		
		$parentValues = [];
		$childValues = [];

		foreach ($data as $key => $value) {
			if(strpos($key, $parent) === 0)
				$parentValues[substr($key, strlen($parent)+1)] = $value;

			if(strpos($key, $child) === 0)
				$childValues[substr($key, strlen($child)+1)] = $value;
		}

		$className = get_class($this->model);

		// $parent = new $className::$parentClass($parentValues);
		// $parent->exists = true;
		// $parent->wasRecentlyCreated = true;

		$parent = new $className::$parentClass();
		$parent->setAttributesArray($parentValues);
        $parent->syncOriginal();
		$parent->exists = true;
  		$parent->wasRecentlyCreated = true;

		// $child = new $className::$childClass($childValues);
		// $child->exists = true;
		// $child->wasRecentlyCreated = true;

		$child = new $className::$childClass();
		$child->setAttributesArray($childValues);
        $child->syncOriginal();
		$child->exists = true;
  		$child->wasRecentlyCreated = true;

  		$this->model->setHierarcy($parent,$child);

  		return $this->model;
	}

	public function renameColumns(){
		$parent = $this->getParentTable();
		$child = $this->getChildTable();
		$className = get_class($this->model);

		$keys = [];
		foreach ($className::$parentKeys as $key)
			$keys[] = "$parent.$key as $parent.$key";

		foreach ($className::$childKeys as $key)
			$keys[] = "$child.$key as $child.$key";

		return $keys;
	}

	// -----------------------------------------------
	// Get Table Names
	// -----------------------------------------------

	public function getParentTable(){
		$className = get_class($this->model);
		return (new $className::$parentClass)->getTable();
	}

	public function getChildTable(){
		$className = get_class($this->model);
		return (new $className::$childClass)->getTable();
	}

	// -----------------------------------------------
	// Enable Chaining queries from Container Object
	// -----------------------------------------------

	public function allowChaining(){
		return $this->model;
	}

	// -----------------------------------------------
	// Redifine some queryBuilder methods
	// -----------------------------------------------

	public function findParent($id){
		$parent = $this->getParentTable();
		return $this->where("$parent.id", $id)->first();
	}

	public function findChild($id){
		$child = $this->getChildTable();
		return $this->where("$child.id", $id)->first();
	}

	public function find($id){
		return $this->findChild($id);
	}

	public function first(){
		$data = $this->query->first($this->renameColumns());
		if(is_null($data)) return null;

		return $this->createModelsFromQuery($data);
	}

	public function get(){
		$data = $this->query->get($this->renameColumns());
		
		$items = [];
		foreach ($data as $item) {
			if(!is_null($item)){
				$items[] = $this->model->build()->createModelsFromQuery($item);
			} 
		}
		return new Collection($items);
	}

	// -----------------------------------------------
	// Route queryBuilder methods to Builder | Parent ModelComposer
	// -----------------------------------------------

	protected static function callObjectMethod($object, $method, $args){
		return call_user_func_array([$object, $method], $args);
	}

	public function method_exists($method){
		return method_exists($this, $method) || method_exists($this->query, $method);
	}

	public function __call($method, $arguments) {
		if(method_exists($this->query, $method)){
			static::callObjectMethod($this->query, $method, $arguments);
			return $this;
		}

		throw new \Exception(__CLASS__.": Method '$method' not found", 1);
	}
}