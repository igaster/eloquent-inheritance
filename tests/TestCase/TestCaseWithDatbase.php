<?php namespace igaster\EloquentInheritance\Tests\TestCase;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Orchestra\Testbench\TestCase;

class TestCaseWithDatbase extends TestCase
{

    // -----------------------------------------------
    //  Testcase Initialize: Setup Database/Load .env
    // -----------------------------------------------

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        
        if (file_exists(__DIR__.'/../.env')) {
            $dotenv = new Dotenv\Dotenv(__DIR__.'/../');
            $dotenv->load();
        }
    }


    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing'); // sqlite , memory
    }


    // -----------------------------------------------
    //  Helpers
    // -----------------------------------------------

    public function reloadModel(&$model){
        $className = get_class($model);
        $model = $className::find($model->id);
        return $model;
    }

    // -----------------------------------------------

    public function testDatabaseConnection()
    {
        $this->assertInstanceOf('Illuminate\Database\SQLiteConnection', \DB::connection());
    }

}    