<?php namespace igaster\EloquentInheritance\Tests\App;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Foo extends Eloquent
{
    protected $table = 'foo';
	public $timestamps = false;
	protected $fillable = ['a','z'];
}