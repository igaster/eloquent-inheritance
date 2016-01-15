<?php

use igaster\EloquentInheritance\Tests\TestCase\TestCaseWithDatbase;

use igaster\EloquentInheritance\ModelComposer;

use igaster\EloquentInheritance\Tests\App\Foo;
use igaster\EloquentInheritance\Tests\App\Bar;
use igaster\EloquentInheritance\Tests\App\BarExtendsFoo;

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
        // Create 1st set of Models
        $foo = Foo::create([
            'a' => 1,
        ]);

        Bar::create([
            'b' => 2,
        	'foo_id' => $foo->id,
        ]);

        // Create 2nd set of Models
        $foo = Foo::create([
            'id'=>5,
            'a' => 10,
        ]);

        Bar::create([
            'id' => 6,
            'b' => 20,
            'foo_id' => $foo->id,
        ]);
    }

    // -----------------------------------------------
    //  Tests
    // -----------------------------------------------

    public function test_load_models(){
        $fooBar = BarExtendsFoo::build()->first();

        $this->assertInstanceOf(Foo::class, $fooBar->parent());
        $this->assertInstanceOf(Bar::class, $fooBar->child());

    }

    public function test_access_properties(){
        $fooBar = BarExtendsFoo::build()->first();

        $this->assertEquals(1, $fooBar->parent()->a);
        $this->assertEquals(2, $fooBar->child()->b);

        $this->assertEquals(1, $fooBar->a);
        $this->assertEquals(2, $fooBar->b);

        $fooBar = BarExtendsFoo::build()->findParent(5);
        $this->assertEquals(10, $fooBar->a);
        $this->assertEquals(20, $fooBar->b);
        $this->assertEquals(6, $fooBar->id);
    }

    public function test_set_properties(){
        $fooBar = BarExtendsFoo::build()->first();

        $fooBar->a=11;
        $fooBar->b=12;

        $this->assertEquals(11, $fooBar->parent()->a);
        $this->assertEquals(12, $fooBar->child()->b);

        $this->assertEquals(11, $fooBar->a);
        $this->assertEquals(12, $fooBar->b);
    }


    public function test_save(){
        $fooBar = BarExtendsFoo::build()->first();

        $fooBar->a=11;
        $fooBar->b=12;
        $fooBar->save();

        $this->assertEquals(11, Foo::first()->a);
        $this->assertEquals(12, Bar::first()->b);
    }

    public function test_query_first(){
        $fooBar = BarExtendsFoo::build()->first();
        $this->assertEquals(1, $fooBar->a);
        $this->assertEquals(2, $fooBar->b);
    }

    public function test_query_find(){
        $fooBar = BarExtendsFoo::build()->findParent(5);
        $this->assertEquals(10, $fooBar->a);

        $fooBar = BarExtendsFoo::build()->findChild(6);
        $this->assertEquals(10, $fooBar->a);

        $fooBar = BarExtendsFoo::build()->find(6);
        $this->assertEquals(10, $fooBar->a);
    }

    public function test_query_where(){
        $fooBar = BarExtendsFoo::build()->where('foo.a', 10)->first();
        $this->assertEquals(10, $fooBar->a);
        $this->assertEquals(20, $fooBar->b);

        $fooBar = BarExtendsFoo::build()->where('a', 10)->first();
        $this->assertEquals(10, $fooBar->a);
        $this->assertEquals(20, $fooBar->b);
    }

    public function test_query_get(){
        $collection = BarExtendsFoo::build()->get();
        $this->assertInstanceOf(Illuminate\Database\Eloquent\Collection::class, $collection);
        $this->assertEquals(2, $collection->count());
        $this->assertInstanceOf(BarExtendsFoo::class, $collection->get(0));
        $this->assertInstanceOf(Foo::class, $collection->get(0)->parent());

        $this->assertEquals(1,  $collection->get(0)->a);
        $this->assertEquals(2,  $collection->get(0)->b);
        $this->assertEquals(10, $collection->get(1)->a);
        $this->assertEquals(20, $collection->get(1)->b);
    }


    public function test_query_not_found(){
        $fooBar = BarExtendsFoo::build()->where('a', 100)->first();
        $this->assertNull($fooBar);

        $fooBar = BarExtendsFoo::build()->where('a', 100)->get();
        $this->assertTrue($fooBar->isEmpty());
    }

    public function test_set_hierarcy(){
        $foo = Foo::create([
            'a' => 100,
        ]);

        $bar = Bar::create([
            'b' => 200,
        ]);

        $fooBar = new BarExtendsFoo;

        $fooBar->setHierarcy($foo, $bar);
        $fooBar->save();

        $foo = $this->reloadModel($foo);
        $bar = $this->reloadModel($bar);
        $this->assertEquals($foo->id, $bar->foo_id);

        $fooBar = BarExtendsFoo::build()->findParent($foo->id);
        $this->assertEquals(100, $fooBar->a);
        $this->assertEquals(200, $fooBar->b);
    }

    public function test_set_parent_child(){
        $foo = Foo::create([
            'a' => 100,
        ]);

        $bar = Bar::create([
            'b' => 200,
        ]);

        // Normal order: Parent->Child
        $fooBar = new BarExtendsFoo;
        $fooBar->setParent($foo);
        $fooBar->setChild($bar);
        $this->assertEquals($foo->id, $bar->foo_id);

        // Inverse order: Child->Parent
        $this->reloadModel($foo);
        $this->reloadModel($bar);
        $fooBar = new BarExtendsFoo;
        $fooBar->setChild($bar);
        $fooBar->setParent($foo);
        $this->assertEquals($foo->id, $bar->foo_id);
    }

    public function test_create_from_models(){
        $foo = Foo::create([
            'a' => 100,
        ]);

        $bar = Bar::create([
            'b' => 200,
        ]);

        $fooBar = BarExtendsFoo::createFrom($foo, $bar);
        $this->reloadModel($foo);
        $this->reloadModel($bar);
        $this->assertEquals($foo->id, $bar->foo_id);
        $this->assertEquals(100, $fooBar->a);
    }

    public function test_create_from_array(){
        $fooBar = BarExtendsFoo::create([
            'a' => 1000,
            'b' => 2000,
            'id' => 99
        ]);

        $fooBar = BarExtendsFoo::build()->find(99);
        $this->assertEquals(1000, $fooBar->a);
        $this->assertEquals(2000, $fooBar->b);
        $this->assertEquals(99, $fooBar->child()->id);        
        $this->assertEquals($fooBar->parent()->id, $fooBar->child()->foo_id);

    }

}