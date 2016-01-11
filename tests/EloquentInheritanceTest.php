<?php

use igaster\EloquentInheritance\Tests\TestCase\TestCaseWithDatbase;

use igaster\EloquentInheritance\ModelComposer;
use igaster\EloquentInheritance\InheritsEloquent;

use igaster\EloquentInheritance\Tests\App\Foo;
use igaster\EloquentInheritance\Tests\App\Bar;

class EloquentInheritanceTest extends TestCaseWithDatbase{

    // -----------------------------------------------
    //  Create the World (Run before each Test)
    // -----------------------------------------------

    public function setUp()
    {
        parent::setUp();

        // -- Set  migrations
        $this->database->schema()->create('foo', function ($table) {
            $table->increments('id');
            $table->integer('a')->nullable();
        });
        $this->database->schema()->create('bar', function ($table) {
            $table->increments('id');
            $table->integer('foo_id')->nullable();
            $table->integer('b')->nullable();
        });

        $this->loadModels();
    }

    public function _tearDown() {
        $this->database->schema()->drop('foo');
        $this->database->schema()->drop('bar');
        parent::teadDown();
    }

    // -----------------------------------------------
    //  Helper Methods
    // -----------------------------------------------

    public $foo;
    public $bar;

    public function loadModels(){
        // Create Test Models
        $this->foo = Foo::create([
            'a' => 1,
        ]);

        $this->bar = Bar::create([
            'b' => 2,
        	'foo_id' => $this->foo->id,
        ]);
    }

    // -----------------------------------------------
    //  Tests
    // -----------------------------------------------

    public function test_xxx(){
        $composer = InheritsEloquent::query()->first();

       // dd($this->foo);
       dd($composer->parent);
       dd(InheritsEloquent::renameColumns());
       dd(InheritsEloquent::query()->find(1));
    }

}