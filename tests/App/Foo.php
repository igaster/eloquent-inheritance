<?php namespace igaster\EloquentInheritance\Tests\App;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Foo extends Eloquent
{
    protected $table = 'foo';
	public $timestamps = false;

	public $foo_property = 5;

	public function fooMethod($a, $b){
		return 1+2;
	}


	private $mutatedValue = "foo";

	public function getMutatedAttribute(){
		return $this->mutatedValue;
	}

	public function setMutatedAttribute($value){
		$this->mutatedValue = $value;
	}

}