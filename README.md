[![Laravel](https://img.shields.io/badge/Laravel-5.x-orange.svg)](http://laravel.com)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg)](https://tldrlegal.com/license/mit-license)
[![Downloads](https://img.shields.io/packagist/dt/igaster/eloquent-inheritance.svg)](https://packagist.org/packages/igaster/eloquent-inheritance)
[![Build Status](https://img.shields.io/travis/igaster/eloquent-inheritance.svg)](https://travis-ci.org/igaster/eloquent-inheritance)
[![Codecov](https://img.shields.io/codecov/c/github/igaster/eloquent-inheritance.svg)](https://codecov.io/github/igaster/eloquent-inheritance)

## Description
Eloquent Multiple Table Inheritance: Use a One-To-One (or One-To-Many) relation between a parent & child Tables to simulate inheritance between Models.

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

// Model Bar inherits Foo.
Schema::create('bar', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('b');
    $table->integer('foo_id')->nullable(); // Foreign Key to Foo
});
```

#### Example Models:

Your models should use the `EloquentInherited` Trait

```php
class Foo extends Eloquent
{
    use \igaster\EloquentInheritance\EloquentInherited;

	// ...
    public function fooMethod(){}
}

class Bar extends Eloquent
{
    use \igaster\EloquentInheritance\EloquentInherited;

	// ...
    public function barMethod(){}
}

class BarExtendsFoo extends igaster\EloquentInheritance\InheritsEloquent{
    // Set Parent/Child classes
    public static $parentClass = Foo::class;
    public static $childClass  = Bar::class;

    // You must declare Parent/Child keys explicity:
    public static $parentKeys = ['id','a'];
    public static $childKeys  = ['id','b','foo_id'];

    // Childs Foreign Key (points to Parent)
    public static $childFK  = 'foo_id';

    // You can add your functions / variables ...
    public function newMethod(){}
}
```

####  Usage:

```php
// Create a composite Model:
$fooBar = BarExtendsFoo::create([ // Creates and Saves Foo & Bar in the Database
    'a' => 1,
    'b' => 2,
]);

// Access Attributes:
$fooBar->a; // = 1 (from Foo model)
$fooBar->b; // = 2 (from Bar model)

// Call Methods:
$fooBar->fooMethod(); // from Foo Model
$fooBar->barMethod(); // from Bar Model
$fooBar->newMethod(); // from self

// Query as an Eloquent model:
$fooBar = BarExtendsFoo::find(1);
$fooBar = BarExtendsFoo::where('a',1)->first();
$fooBar = BarExtendsFoo::get();     // Collection of BarExtendsFoo 

$fooBar->save();    // Saves Foo & Bar
$fooBar->delete();  // Deletes Foo & Bar
$fooBar->update([   // Updates Foo & Bar
    'a' => 10,
    'b' => 20,
]);
```