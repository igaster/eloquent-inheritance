<?php namespace igaster\EloquentInheritance;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Collection;

class InheritsEloquent extends ModelComposer{
	public $query = null;

	// -----------------------------------------------
	// Factories
	// -----------------------------------------------

	public static function build(){
		$instance = new static;

		$parent = self::getParentTable();
		$child = self::getChildTable();
		$childFK = static::$childFK;

		$instance->query =  DB::table($parent)
					->leftJoin($child, "$parent.id", '=', "$child.$childFK");

		return $instance;
	}

	public static function createFrom($parent, $child){
		return static::build()->setHierarcy($parent, $child)->save();
	}

	public static function create($data){
		$parent = new static::$parentClass;
		$child  = new static::$childClass;

		foreach ($data as $key => $value) {
			if(in_array($key, static::$childKeys)){
				$child->$key = $value;
			} else {
				$parent->$key = $value;				
			}
		}
		$parent->save();
		return static::createFrom($parent, $child);
	}


	// -----------------------------------------------
	// Get Table Names
	// -----------------------------------------------

	private static function getParentTable(){
		return (new static::$parentClass)->getTable();
	}

	private static function getChildTable(){
		return (new static::$childClass)->getTable();
	}

	// -----------------------------------------------
	// Get Models
	// -----------------------------------------------

	public function parent(){
		return isset($this->models[1]) ? $this->models[1] : null;
	}

	public function child(){
		return isset($this->models[0]) ? $this->models[0] : null;
	}

	// -----------------------------------------------
	// Set Models
	// -----------------------------------------------

	public function setHierarcy($parent, $child){
		$childFK = static::$childFK;

		$this->models[1] = $parent;
		$this->models[0] = $child;

		if (!empty($parent) && !empty($child)) {
			$child->$childFK = $parent->id;
		}

		return $this;
	}

	public function setParent($parent){
		return $this->setHierarcy($parent, $this->child());
	}

	public function setChild($child){
		return $this->setHierarcy($this->parent(), $child);
	}

	// -----------------------------------------------
	// Break / Compose models
	// -----------------------------------------------

	public function createModelsFromQuery($data = []){
		$parent = self::getParentTable();
		$child = self::getChildTable();
		
		$parentValues = [];

		foreach ($data as $key => $value) {
			if(strpos($key, $parent) === 0)
				$parentValues[substr($key, strlen($parent)+1)] = $value;

			if(strpos($key, $child) === 0)
				$childValues[substr($key, strlen($child)+1)] = $value;
		}

		$parent = new static::$parentClass($parentValues);
		$parent->exists = true;
  		$parent->wasRecentlyCreated = true;

		$child = new static::$childClass($childValues);
		$child->exists = true;
  		$child->wasRecentlyCreated = true;

  		$this->addModel($parent);
  		$this->addModel($child);

  		return $this;
	}

	public static function renameColumns(){
		$parent = self::getParentTable();
		$child = self::getChildTable();

		$keys = [];
		foreach (static::$parentKeys as $key)
			$keys[] = "$parent.$key as $parent.$key";

		foreach (static::$childKeys as $key)
			$keys[] = "$child.$key as $child.$key";

		return $keys;
	}

	// -----------------------------------------------
	// Redifine some queryBuilder methods
	// -----------------------------------------------

	public function findParent($id){
		$parent = self::getParentTable();
		return $this->where("$parent.id", $id)->first();
	}

	public function findChild($id){
		$child = self::getChildTable();
		return $this->where("$child.id", $id)->first();
	}

	public function find($id){
		return $this->findChild($id);
	}

	public function first(){
		$data = $this->query->first(self::renameColumns());
		if(is_null($data)) return null;

		$this->createModelsFromQuery($data);
		return $this;
	}

	public function get(){
		$data = $this->query->get(self::renameColumns());
		
		$items = [];
		foreach ($data as $item) {
			if(!is_null($item)){
				$items[] = static::build()->createModelsFromQuery($item);
			} 
		}
        return new Collection($items);
	}

	public function save(){
		parent::save();
		return $this;
	}

	// -----------------------------------------------
	// Route queryBuilder methods to internal Builder
	// -----------------------------------------------

	public function __call($name, $arguments) {
		if(method_exists($this->query, $name)){
			static::callObjectMethod($this->query, $name, $arguments);
			return $this;
		}

		return parent::__call($name, $arguments);
	}
}