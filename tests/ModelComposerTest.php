<?php

use igaster\EloquentInheritance\Tests\TestCase\TestCaseWithDatbase;

use igaster\EloquentInheritance\ModelComposer;

use igaster\EloquentInheritance\Tests\App\Foo;
use igaster\EloquentInheritance\Tests\App\Bar;

class ModelComposerTest extends TestCaseWithDatbase
{
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
            $table->integer('z')->nullable();
        });
        $this->database->schema()->create('bar', function ($table) {
            $table->increments('id');
            $table->integer('b')->nullable();
            $table->integer('z')->nullable();
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
    public $composer;

    public function loadModels(){
        // Create Test Models
        $this->foo = Foo::create([
            'a' => 1,
            'z' => 2,
        ]);

        $this->bar = Bar::create([
            'b' => 3,
            'z' => 4,
        ]);

        $this->composer = new ModelComposer();
        $this->composer->addModel($this->foo);
        $this->composer->addModel($this->bar);
    }


    public function reloadModel($model){
        $className = get_class($model);
        return $className::find($model->id);
    }

    // -----------------------------------------------
    //  Tests
    // -----------------------------------------------

    public function test_property_get(){
        $this->assertEquals(1, $this->composer->getPropertyValue('a'));
        $this->assertEquals(4, $this->composer->getPropertyValue('z'));
        $this->assertEquals(3, $this->composer->getPropertyValue('b'));
        $this->assertEquals(5, $this->composer->getPropertyValue('foo_property'));
    }
    
    public function test_invalid_property_get(){
        $this->setExpectedException(\Exception::class);
        $this->composer->getPropertyValue('invalid');
    }


    public function test_invalid_property_set(){
        $this->setExpectedException(\Exception::class);
        $this->composer->setPropertyValue('invalid', null);
    }

    public function test_property_set(){
        $this->composer->setPropertyValue('a', 11);
        $this->composer->setPropertyValue('b', 12);
        $this->composer->setPropertyValue('z', 13);

        $this->assertEquals(11, $this->composer->getPropertyValue('a'));
        $this->assertEquals(12, $this->composer->getPropertyValue('b'));
        $this->assertEquals(13, $this->composer->getPropertyValue('z'));

        $this->assertEquals(11, $this->foo->a);
        $this->assertEquals(12, $this->bar->b);
        $this->assertEquals(13, $this->bar->z);
        $this->assertEquals(2,  $this->foo->z);
    }

    public function test_object_property(){
        $this->composer->setPropertyValue('foo_property', 15);
        $this->assertEquals(15, $this->composer->getPropertyValue('foo_property'));
        $this->assertEquals(15, $this->foo->foo_property);
    }

    public function test_property_access(){
        $this->composer->a = 11;
        $this->composer->foo_property = 15;

        $this->assertEquals(11, $this->foo->a);
        $this->assertEquals(15, $this->foo->foo_property);
    }

    public function test_method_call(){
        $this->assertEquals(3, $this->composer->fooMethod(1,2));
    }

    public function test_mutator_get(){
        $this->assertEquals($this->foo->mutated, $this->composer->mutated);
    }

    public function test_mutator_set(){
        $this->composer->mutated = 'test';
        $this->assertEquals('test', $this->foo->mutated);
    }

    public function test_invalid_method_call(){
        $this->setExpectedException(\Exception::class);
        $this->assertEquals(3, $this->composer->invalidMethod());
    }

}