<?php namespace igaster\EloquentInheritance;

use Illuminate\Database\Eloquent\Model;


use igaster\EloquentInheritance\Tests\App\Foo as Foo;
use igaster\EloquentInheritance\Tests\App\Bar as Bar;


trait InheritsEloquent {

	public $parent = null;
	private $touchedParent = false;

	public static function getParentClass(){
		return self::$inheritsEloquent;
	}

	public static function getInheritedKeys(){
		return self::$inheritsKeys;
	}

	// convention: parentTable_id
	public function getForeignKey(){
		$parentClass = self::getParentClass();
        $parentTable =  ((new $parentClass)->getTable());
		return $parentTable.'_id';
	}

	public function getParent(){
		if(!$this->parent){
			$parentClass = self::getParentClass();
			$foreignKey = $this->getForeignKey();
			$this->parent = $parentClass::where('id', $this->$foreignKey)->first();
		}
		return $this->parent;
	}

	public function hasParent(){
		if ($this->parent) return true;
		$parentClass = self::getParentClass();
		$foreignKey = $this->getForeignKey();
		return (!empty($this->$foreignKey));
	}

	public function setParent(Model $model){
		$this->parent = $model;
		$foreignKey = $this->getForeignKey();
		$this->attributes[$foreignKey] = $model->id;
		return $this;
	}

	public function getParentValue($key){
		if (!$parent){
			$parentClass = self::getParentClass();
		}
	}

	public function createParent(array $attributes = []){
		$parentClass = self::getParentClass();
		$parent = $parentClass::create($attributes);
		$this->setParent($parent);
		$this->save();
		return $this;
	}

    public function save(array $options = []){
    	parent::save($options);
    	
    	if($this->touchedParent){
    		$this->getParent()->save();;
    		$this->touchedParent = false;
    	}
    }

    public function __get($name){
    	$parentClass = (new \ReflectionClass(self::getParentClass()))->getShortName();
    	if(strcasecmp($name,$parentClass)==0)
    		return $this->getParent();

    	if(in_array($name, self::getInheritedKeys())){
    		return $this->getParent()->$name;
    	}
    	return parent::__get($name);
    }

    public function __set($name, $value){
    	if(in_array($name, self::getInheritedKeys())){
    		$this->touchedParent = true;
    		return $this->getParent()->$name = $value;
    	}
    	return parent::__set($name, $value);
    }
}