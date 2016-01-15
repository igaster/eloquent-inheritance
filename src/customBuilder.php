<?php namespace igaster\EloquentInheritance;

// use Illuminate\Database\Eloquent\Builder;
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

		$this->createModelsFromQuery($data);
		return $this->model;
	}

	public function get(){
		$data = $this->query->get($this->renameColumns());
		
		$items = [];
		foreach ($data as $item) {
			if(!is_null($item)){
				$items[] = $this->build()->createModelsFromQuery($item);
			} 
		}
        return new Collection($items);
	}

	public function save(){
		parent::save();
		return $this->model;
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
			return static::callObjectMethod($this->query, $method, $arguments);
		}


		if(method_exists($this->model, $method)){
			return static::callObjectMethod($this->model, $method, $arguments);
		}

		throw new Exception("Method '$method' not found", 1);
	}
}