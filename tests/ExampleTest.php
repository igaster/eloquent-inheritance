<?php

use igaster\EloquentInheritance\Tests\TestCase\TestCaseWithDatbase;
use Orchestra\Testbench\TestCase;


use igaster\EloquentInheritance\Tests\App\Foo;
use igaster\EloquentInheritance\Tests\App\Bar;

class ExampleTest extends TestCaseWithDatbase
{
    // -----------------------------------------------
    //  Setup Database
    // -----------------------------------------------

    private $model;
    public function setUp()
    {
        parent::setUp();
        
        // -- Set  migrations
        $this->database->schema()->create('foo', function ($table) {
            $table->increments('id');
            $table->integer('a')->nullable();
            $table->integer('z')->nullable();
        });
        $this->database->schema()->create('bar', function ($table) {
            $table->increments('id');
            $table->integer('foo_id')->nullable();
            $table->integer('b')->nullable();
            $table->integer('z')->nullable();
        });
        
    }

    public function tearDown() {
        $this->database->schema()->drop('foo');
        $this->database->schema()->drop('bar');
    }

    // -----------------------------------------------
    //  Create new Model
    // -----------------------------------------------

    public function reloadModel($model){
        $className = get_class($model);
        return $className::find($model->id);
    }

    public function newModels() {
        $foo = Foo::create([
            'a' => 1,
            'z' => 2,
        ]);

        $bar = Bar::create([
            'b' => 3,
            'z' => 4,
        ]);

        $bar->setParent($foo)->save();

        return [$foo, $bar];        
    }


    // -----------------------------------------------
    //  Tests
    // -----------------------------------------------

    public function testTrait(){
        list($foo, $bar) = $this->newModels();
        $this->assertInstanceOf(Foo::class, $foo);
        $this->assertInstanceOf(Bar::class, $bar);
        $this->assertEquals(Foo::class, $bar->getParentClass());
    }


    public function test_parent_relationship(){
        list($foo, $bar) = $this->newModels();

        $this->assertTrue($bar->hasParent());
        $this->assertEquals($foo, $bar->getParent());
        $this->assertEquals('foo_id', $bar->getForeignKey());

        // Reload from DB and run tests again 
        $foo = $this->reloadModel($foo);
        $bar = $this->reloadModel($bar);
        $this->assertTrue($bar->hasParent());
        $this->assertEquals('foo_id', $bar->getForeignKey());
    }

    public function test_parent_access_as_property(){
        list($foo, $bar) = $this->newModels();
        $this->assertEquals($foo, $bar->foo);

        // Reload from DB and run tests again 
        $bar = $this->reloadModel($bar);
        $foo = $this->reloadModel($foo);
        $this->assertEquals($foo, $bar->foo);
    }

    public function test_properties_get(){
        list($foo, $bar) = $this->newModels();

        $this->assertEquals(1, $foo->a);
        $this->assertEquals(2, $foo->z);

        $this->assertEquals(1, $bar->a);
        $this->assertEquals(3, $bar->b);
        $this->assertEquals(4, $bar->z);

        // Reload from DB and tests again 
        $foo = $this->reloadModel($foo);
        $bar = $this->reloadModel($bar);

        $this->assertEquals(1, $foo->a);
        $this->assertEquals(2, $foo->z);

        $this->assertEquals(1, $bar->a);
        $this->assertEquals(3, $bar->b);
        $this->assertEquals(4, $bar->z);
    }

    public function test_properties_set(){
        list($foo, $bar) = $this->newModels();
        $bar->setParent($foo);

        $bar->a=11;
        $this->assertEquals(11, $bar->a);
        $this->assertEquals(11, $foo->a);

        $bar->save();
        $bar = $this->reloadModel($bar);
        $this->assertEquals(11, $bar->a);
    }

    public function test_properties_create_with_parent(){
        $bar = Bar::create([
            'b' => 3,
            'z' => 4,
        ])->createParent([
            'a' => 1,
            'z' => 2,
        ]);

        $this->assertInstanceOf(Foo::class, $bar->foo);
        $this->assertEquals(1, $bar->a);
        $this->assertEquals(3, $bar->b);
        $this->assertEquals(4, $bar->z);

        $this->assertEquals(2, $bar->foo->z);
        $this->assertEquals(1, $bar->foo->a);
    }

}