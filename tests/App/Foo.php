<?php namespace igaster\EloquentInheritance\Tests\App;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Foo extends Eloquent
{
    protected $table = 'foo';
    protected $guarded = [];
	public $timestamps = false;


	// Test Properties
	
	public $foo_property = 5;

	// Test methods

	public function fooMethod($a, $b){
		return 1+2;
	}

	// Test Functions as Attributes

	public function otherModel(){
		return $this->belongsTo(Bar::class, 'id', 'id');
	}

	// Test mutators

	private $mutatedValue = "foo";

	public function getMutatedAttribute(){
		return $this->mutatedValue;
	}

	public function setMutatedAttribute($value){
		$this->mutatedValue = $value;
	}

}