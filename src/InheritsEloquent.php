<?php namespace igaster\EloquentInheritance;


use igaster\EloquentInheritance\Tests\App\Foo as Foo;
use igaster\EloquentInheritance\Tests\App\Bar as Bar;
use Illuminate\Database\Capsule\Manager as DB;


class InheritsEloquent extends ModelComposer{
	public static $parentClass = Foo::class;
	public static $childClass = Bar::class;

	public static $parentKeys = ['id','a'];
	public static $childKeys = ['id','b','foo_id'];

	public $query = null;
	public $parent = null;
	public $child = null;

	private static function getParentTable(){
		return (new static::$parentClass)->getTable();
	}

	private static function getChildTable(){
		return (new static::$childClass)->getTable();
	}

	public static function query(){
		$instance = new static;

		$parent = self::getParentTable();
		$child = self::getChildTable();

		$instance->query =  DB::table($parent)
					->leftJoin($child, "$parent.id", '=', "$child.foo_id");

		return $instance;
	}

	public function createModelsFromQueryResult($data = []){
		$parent = self::getParentTable();
		$child = self::getChildTable();
		
		$parentValues = [];

		foreach ($data as $key => $value) {
			if(strpos($key, $parent) === 0)
				$parentValues[substr($key, strlen($parent)+1)] = $value;

			if(strpos($key, $child) === 0)
				$childValues[substr($key, strlen($child)+1)] = $value;
		}

		$this->parent = new self::$parentClass($parentValues);
		$this->parent->exists = true;
  		$this->parent->wasRecentlyCreated = true;

		$this->child = new self::$childClass($childValues);
		$this->child->exists = true;
  		$this->child->wasRecentlyCreated = true;
	}

	public static function renameColumns(){
		$parent = self::getParentTable();
		$child = self::getChildTable();

		$keys = [];
		foreach (self::$parentKeys as $key)
			$keys[] = "$parent.$key as $parent.$key";

		foreach (self::$childKeys as $key)
			$keys[] = "$child.$key as $child.$key";

		return $keys;
	}

	public function find($id){
		$parent = self::getParentTable();
		$child = self::getChildTable();
		return $this->where("$parent.id", $id)->first();
	}

	public function first(){
		$data = $this->query->first(self::renameColumns());
		$this->createModelsFromQueryResult($data);
		return $this;
	}

	// private static function call_method($object, $method, $arguments){
	// 	if(count($arguments)==1){
	// 		list($a1,$a2) = $arguments;
	// 		return $object->$method($a1);
	// 	}elseif(count($arguments)==2){
	// 		list($a1,$a2) = $arguments;
	// 		return $object->$method($a1,$a2);
	// 	}elseif(count($arguments)==3){
	// 		list($a1,$a2,$a3) = $arguments;
	// 		return $object->$method($a1,$a2,$a3);
	// 	}elseif(count($arguments)==4){
	// 		list($a1,$a2,$a3,$a4) = $arguments;
	// 		return $object->$method($a1,$a2,$a3,$a4);
	// 	}elseif(count($arguments)==5){
	// 		list($a1,$a2,$a3,$a4,$a5) = $arguments;
	// 		return $object->$method($a1,$a2,$a3,$a4,$a5);
	// 	}
	// }

	// public function __call($name, $arguments) {
	// 	$queryBuilderMethods=['where','join','leftJoin','first','get'];

	// 	if(in_array($name, $queryBuilderMethods)){
	// 		static::call_method($this->query, $name, $arguments);
	// 		return $this;
	// 	}

	// 	return parent::__call($name, $arguments);
	// }

}