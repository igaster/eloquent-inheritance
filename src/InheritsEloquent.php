<?php namespace igaster\EloquentInheritance;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;

class InheritsEloquent extends ModelComposer{
	public $query = null;

	// -----------------------------------------------
	// Factories
	// -----------------------------------------------

	public static function build(){
		$instance = new static;

		$parentTable = $instance->getParentTable();
		$childTable = $instance->getChildTable();
		$childFK = static::$childFK;

		$instance->query =  new customBuilder($instance, \DB::table($parentTable)
					->leftJoin($childTable, "$parentTable.id", '=', "$childTable.$childFK"));

		$instance->setHierarcy(new static::$parentClass, new static::$childClass);

		return $instance;
	}

	public static function createFrom($parent, $child){
		return static::build()->setHierarcy($parent, $child)->save();
	}

	// -----------------------------------------------
	// Get Table Names
	// -----------------------------------------------

	public function getParentTable(){
		return (new static::$parentClass)->getTable();
	}

	public function getChildTable(){
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

		$this->models[0] = $child;
		$this->models[1] = $parent;

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
	// Redifine some queryBuilder methods
	// -----------------------------------------------

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

	public function update($data){
		foreach ($data as $key => $value) {
			$this->setPropertyValue($key,$value);
		}
		return $this->save();
	}

	public function save(){
		parent::save();
		return $this;
	}

	// -----------------------------------------------
	// Route queryBuilder methods to internal Builder
	// -----------------------------------------------

	public static function __callStatic($name, $arguments) {
	    $instance = static::build();
		return $instance->__call($name,$arguments);
	}

	public function __call($name, $arguments) {

		if($this->query->method_exists($name)){
			$result = static::callObjectMethod($this->query, $name, $arguments);
			return is_a($result, customBuilder::class) ? $this : $result;
		}

		return parent::__call($name, $arguments);
	}
}