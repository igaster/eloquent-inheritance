<?php namespace igaster\EloquentInheritance\Tests\App;

use igaster\EloquentInheritance\InheritsEloquent;

class BarExtendsFoo extends InheritsEloquent{

	public static $parentClass = \igaster\EloquentInheritance\Tests\App\Foo::class;
	public static $childClass  = \igaster\EloquentInheritance\Tests\App\Bar::class;

	public static $parentKeys = ['id','a'];
	public static $childKeys  = ['id','b','foo_id'];

	public static $childFK  = 'foo_id';
}