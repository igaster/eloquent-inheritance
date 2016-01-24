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

        \Schema::create('foo', function ($table) {
            $table->increments('id');
            $table->integer('a')->nullable();
            $table->integer('z')->nullable();
        });
        \Schema::create('bar', function ($table) {
            $table->increments('id');
            $table->integer('b')->nullable();
            $table->integer('z')->nullable();
            $table->text('json')->default(json_encode([]));
        });

        $this->loadModels();
    }

    public function _tearDown() {
        \Schema::create('foo');
        \Schema::create('bar');
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

    public function test_get_method_as_property(){
        $this->assertInstanceOf(Bar::class, $this->composer->otherModel);
    }

    public function test_invalid_method_call(){
        $this->setExpectedException(\Exception::class);
        $this->assertEquals(3, $this->composer->invalidMethod());
    }

    public function test_get_model(){
        $this->assertInstanceOf(Foo::class, $this->composer->getModel(0));
        $this->assertInstanceOf(Bar::class, $this->composer->getModel(1));
    }

    public function test_isset(){
        $this->assertTrue(isset($this->composer->a));
        $this->assertTrue(isset($this->composer->b));
        $this->assertTrue(isset($this->composer->z));

        $this->assertTrue(isset($this->composer->mutated));

        $this->assertFalse(isset($this->composer->invalid));
        $this->assertFalse(isset($this->composer->otherModel));
    }


    public function test_save(){
        $this->composer->a = 11;
        $this->composer->b = 12;
        $this->composer->z = 13;
        $this->composer->save();

        $foo = $this->reloadModel($this->foo);
        $bar = $this->reloadModel($this->bar);

        $this->assertEquals(11, $foo->a);
        $this->assertEquals(12, $bar->b);
        $this->assertEquals(13, $bar->z);
        $this->assertEquals(2,  $foo->z);
    }

    public function test_query_scopes(){
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $this->composer->nullScope('parameter'));
    }

    public function test_cast_array(){
        $this->bar->json = [
            1=>11,
            2=>22
        ];

        $this->bar->save();
        $composer = new ModelComposer();
        $composer->addModel($this->foo);
        $composer->addModel($this->reloadModel($this->bar));

        $this->assertInternalType('array',$this->composer->json);
        $this->assertEquals(11, $this->composer->json[1]);

    }
}