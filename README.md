## Description
[![Laravel](https://img.shields.io/badge/Laravel-5.x-orange.svg)](http://laravel.com)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg)](https://tldrlegal.com/license/mit-license)
[![Downloads](https://img.shields.io/packagist/dt/igaster/eloquent-inheritance.svg)](https://packagist.org/packages/igaster/eloquent-inheritance)
[![Build Status]https://img.shields.io/travis/igaster/eloquent-inheritance.svg)](https://travis-ci.org/igaster/eloquent-inheritance)
[![Codecov](https://img.shields.io/codecov/c/github/igaster/eloquent-inheritance.svg)](https://codecov.io/github/igaster/eloquent-inheritance)

## Description
Eloquent Multiple Table Inheritance

## Installation:

Edit your project's composer.json file to require:

```php
"require": {
    "igaster/eloquent-inheritance": "~1.0"
}
and install with `composer update`
```

## Usage:

#### Example Schema:

```php
// Model Foo is the parent model
Schema::create('foo', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('a')->nullable();
    $table->integer('z')->nullable();
});

// Model Bar inherits Foo. Notice FK naming convention:
Schema::create('bar', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('foo_id')->nullable(); // Foreign Key 
    $table->integer('b')->nullable();
    $table->integer('z')->nullable();
});

// You may add more models that inherit Foo
```

#### Example Models:

```php
class Foo extends Eloquent
{
	// ...
}

class Bar extends Eloquent
{
	use \igaster\EloquentInheritance\InheritsEloquent;
	protected static $inheritsEloquent 	= Foo::class;
	protected static $inheritsKeys 		= ['a'];

	// ...
}
```

#### API reference:

```php
$bar->setParent($foo)->save(); // Set parent object
$bar->getParent() // Get instance of parent
$bar->foo 		  // Same as above! (Access as with parent class name)
```

####  Example

```php
$foo = Foo::create([
    'a' => 1,
    'z' => 2,
]);

$bar = Bar::create([
    'b' => 3,	// Add a new key
    'z' => 4,	// Overide parent key
]);

$bar->setParent($foo)->save();

$bar->a; // 1 parent property
$bar->b; // 3 own property
$bar->z; // 4 ovveride property - hides parent's

$foo->a; // 1
$foo->z; // 2

```

#### Shorthand Creation:

```php
$bar = Bar::create([
    'b' => 3,
    'z' => 4,
])->createParent([
    'a' => 1,
    'z' => 2,
]);
```