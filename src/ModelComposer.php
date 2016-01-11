<?php namespace igaster\EloquentInheritance;

use Illuminate\Database\Eloquent\Model;

use igaster\EloquentInheritance\Tests\App\Foo as Foo;
use igaster\EloquentInheritance\Tests\App\Bar as Bar;

class ModelComposer {

	public $models = [];

	public function addModel(Model $model){
		array_unshift($this->models,$model);
		// $this->models[] = $model;
	}

	public function getPropertyValue($name){
		foreach ($this->models as $model) {
			if(isset($model->$name))
				return($model->$name);

			if (method_exists($model, $method = 'get'.studly_case($name).'Attribute'))
	    		return call_user_func_array([$model, $method], null);
		}


		throw new \Exception("ModelComposer: Property '$name' does not exists in any model", 1);
	}

	public function setPropertyValue($name, $value){
		foreach ($this->models as $model) {
			if(isset($model->$name)){
				$model->$name = $value;
				return $this;
			}
		}
		throw new \Exception("ModelComposer: Property '$name' does not exists in any model", 1);		
	}

	public function callMethod($method, $args){
		foreach ($this->models as $model) {
			if(method_exists($model, $method)){
	    		return call_user_func_array([$model, $method], $args);
			}
		}
		throw new \Exception("ModelComposer: Method '$method' does not exists in any model", 1);		
	}

	public function __call($method, $args){
		return $this->callMethod($method, $args);
	}

	public function __get($name){
		return $this->getPropertyValue($name);
	}

	public function __set($name, $value){
		return $this->setPropertyValue($name, $value);
	}


}