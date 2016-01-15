[![Laravel](https://img.shields.io/badge/Laravel-5.x-orange.svg)](http://laravel.com)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg)](https://tldrlegal.com/license/mit-license)
[![Downloads](https://img.shields.io/packagist/dt/igaster/eloquent-inheritance.svg)](https://packagist.org/packages/igaster/eloquent-inheritance)
[![Build Status](https://img.shields.io/travis/igaster/eloquent-inheritance.svg)](https://travis-ci.org/igaster/eloquent-inheritance)
[![Codecov](https://img.shields.io/codecov/c/github/igaster/eloquent-inheritance.svg)](https://codecov.io/github/igaster/eloquent-inheritance)

## Description
Eloquent Multiple Table Inheritance.

## Installation:

Edit your project's composer.json file to require:

```php
"require": {
    "igaster/eloquent-inheritance": "~1.0"
}
```
and install with `composer update`

## Usage:

#### Example Schema:

```php
// Model Foo is the parent model
Schema::create('foo', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('a');
});

// Model Bar inherits Foo. Notice Foreign Key naming convention:
Schema::create('bar', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('foo_id')->nullable(); // FK: parentTableName_id
    $table->integer('b');
});

// You may add more models that inherit Foo
```

#### Example Models:

```php
class Foo extends Eloquent
{
	// ...
    public function fooMethod(){}
}

class Bar extends Eloquent
{
	// ...
    public function barMethod(){}
}

class BarExtendsFoo extends igaster\EloquentInheritance\InheritsEloquent{
    public static $parentClass = Foo::class;
    public static $childClass  = Bar::class;

    public static $parentKeys = ['id','a'];
    public static $childKeys  = ['id','b','foo_id'];

    public static $childFK  = 'foo_id';

    // Add your functions / variables...
    public function newMethod(){}
}
```


####  Example

```php
// Create a composite Model:
$fooBar = BarExtendsFoo::create([
    'a' => 1,
    'b' => 2,
]);


// Access Attributes
$fooBar->a; // = 1 (from Foo model)
$fooBar->b; // = 2 (from Bar model)

// Call Methods
$fooBar->fooMethod(); // from Foo Model
$fooBar->barMethod(); // from Bar Model
$fooBar->newMethod(); // from self

// Load: Start your queries with BarExtendsFoo::build()
$fooBar = BarExtendsFoo::build()->find(1);
$fooBar = BarExtendsFoo::build()->where('a',10)->first();
$fooBar = BarExtendsFoo::build()->get(); // Collection of BarExtendsFoo 

$fooBar->save();    // Saves Foo + Bar
$fooBar->delete();  // Deletes Foo + Bar
```
