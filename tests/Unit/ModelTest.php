<?php

namespace DanBoehm\Articulant\Tests\Unit;


use DanBoehm\Articulant\Model;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

/**
 * Class ModelTest
 *
 * @package Tests\Unit
 * @see Model
 * @method Model makeUUT(...$arguments)
 */
class ModelTest extends AbstractUnitTestCase
{
    /** @inheritdoc */
    public static function getCUT() : string
    {
        return Model::class;
    }

    /** @see Model::booted() */
    public function testValidateBeforeSave() : void
    {
        $uut = new ModelTest_Model();
        $uut->rules = ['foo' => 'required'];

        static::assertFalse(static::invokeMethod($uut, 'fireModelEvent', ['saving']));

        $uut->setAttribute('foo', 'bar');

        static::assertNull(static::invokeMethod($uut, 'fireModelEvent', ['saving']));
    }

    /** @see Model::booted() */
    public function testRefreshAfterSave() : void
    {
        Schema::create('model_test_models', function(Blueprint $blueprint) : void {
            $blueprint->id();
            $blueprint->string('foo')->default($this->faker->word);
        });

        $uut = ModelTest_Model::withoutEvents(static function() : ModelTest_Model {
            $uut = new ModelTest_Model();
            $uut->setTable('model_test_models');
            $uut->save();

            return $uut;
        });

        static::assertArrayNotHasKey('foo', $uut->getAttributes()); // This is just here to make sure the test is working correctly.

        static::invokeMethod($uut, 'fireModelEvent', ['saved']);

        static::assertArrayHasKey('foo', $uut->getAttributes());
        static::assertNotNull($uut->foo);
    }

    /** @see Model::usesSoftDeletes() */
    public function test_usesSoftDeletes() : void
    {
        $uut = new ModelTest_Model();

        static::assertFalse($uut::usesSoftDeletes());

        $uut = new ModelTest_SoftDeleteableModel();

        static::assertTrue($uut::usesSoftDeletes());
    }
}

/**
 * Class ModelTest_Model
 *
 * This is just a helper class for testing the abstract model.
 */
class ModelTest_Model extends Model
{
    public $rules = [];

    /**
     * @inheritDoc
     */
    protected function getValidator() : Validator
    {
        return ValidatorFacade::make($this->attributes, $this->rules);
    }
}

/**
 * Class ModelTest_SoftDeleteableModel
 *
 * This is just a helper class for testing the abstract model.
 */
class ModelTest_SoftDeleteableModel extends ModelTest_Model
{
    use SoftDeletes;
}