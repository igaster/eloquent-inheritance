<?php namespace igaster\EloquentInheritance;

use Illuminate\Database\Eloquent\Model;

class ModelComposer {

	public $models = [];

	public function addModel(Model $model){
		array_unshift($this->models,$model);
	}

	public function getModel($index){
		$index = count($this->models)-$index-1;
		return isset($this->models[$index]) ? $this->models[$index] : null;
	}

	public function setModel($index, $model){
		$this->models[count($this->models)-$index-1] = $model;
	}

	public function save(){
		foreach ($this->models as $model)
			if(!empty($model))
				$model->save();
	}

	public function delete(){
		foreach ($this->models as $model)
			if(!empty($model))
				$model->delete();
	}

	public function getPropertyValue($name){
		foreach ($this->models as $model) {

			if(isset($model->$name))
				return($model->$name);

			// Handel mutators: getXxxxxAttribute()
			if($result = $model->getRelationValue($name))
				return $result;
        
		}
		throw new \Exception(__CLASS__.": Property '$name' does not exists in any model", 1);
	}

	public function setPropertyValue($name, $value){
		foreach ($this->models as $model) {
			if(isset($model->$name)){
				$model->$name = $value;
				return $this;
			}
		}
		throw new \Exception(__CLASS__.": Property '$name' does not exists in any model", 1);		
	}

	protected static function callObjectMethod($object, $method, $args){
		return call_user_func_array([$object, $method], $args);
	}

	protected function callMethod($method, $args){
		foreach ($this->models as $model) {
			if(method_exists($model, $method)){
	    		return static::callObjectMethod($model, $method, $args);
			}
			if (method_exists($model, $scope = 'scope'.ucfirst($method))) {
	    		return static::callObjectMethod($model, $method, $args);
			    // return $this->callScope($scope, $parameters);
			}			
		}
		throw new \Exception(__CLASS__.": Method '$method' does not exists in any model", 1);		
	}

	protected function propertyExists($name){
		foreach ($this->models as $model) {
			if(isset($model->$name)){
				return true;
			}
		}
		return false;
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

    public function __isset($name) {
		return $this->propertyExists($name);
    }



}