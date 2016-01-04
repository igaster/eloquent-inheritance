## Description
Eloquent Inheritence

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

$foo->a; // 1
$foo->z; // 2

$bar->a; // 1
$bar->b; // 3
$bar->z; // 4

```

#### Shorthand Create :

```php
$bar = Bar::create([
    'b' => 3,
    'z' => 4,
])->createParent([
    'a' => 1,
    'z' => 2,
]);
```