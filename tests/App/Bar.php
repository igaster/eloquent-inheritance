<?php namespace igaster\EloquentInheritance\Tests\App;


use igaster\EloquentInheritance\Tests\App\Foo;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Bar extends Eloquent
{
    protected $table = 'bar';
    protected $guarded = [];
	public $timestamps = false;
}