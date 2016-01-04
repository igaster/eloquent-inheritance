<?php namespace igaster\EloquentInheritance\Tests\App;


use igaster\EloquentInheritance\Tests\App\Foo;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Bar extends Eloquent
{
	use \igaster\EloquentInheritance\InheritsEloquent;

    protected $table = 'bar';
	public $timestamps = false;
	protected $fillable = ['b', 'z'];

	protected static $inheritsEloquent = Foo::class;
	protected static $inheritsKeys = ['a'];
}