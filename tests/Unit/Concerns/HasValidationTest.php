<?php

namespace DanBoehm\Articulant\Tests\Unit\Concerns;


use DanBoehm\Articulant\Concerns\HasValidation;
use DanBoehm\Articulant\Tests\Unit\UnitTestCase;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class HasValidationTest
 *
 * @package Tests\Unit\App\Models\Concerns
 * @see HasValidation
 */
class HasValidationTest extends UnitTestCase
{
    /** @inheritdoc  */
    public static function getCUT() : string
    {
        return HasValidation::class;
    }

    /** @see HasValidation::validate() */
    public function test_validate() : void
    {
        /** @var HasValidation|MockObject $uut */
        $uut = $this->getMockForTrait(static::getCUT());
        $uut->method('getValidator')->willReturn(ValidatorFacade::make([], []));

        static::assertTrue($uut->validate());

        /** @var HasValidation|MockObject $uut */
        $uut = $this->getMockForTrait(static::getCUT());
        $uut->method('getValidator')->willReturn(ValidatorFacade::make([], ['foo' => 'required']));

        static::assertFalse($uut->validate());

    }

    /** @see HasValidation::getValidator() */
    public function test_getValidationData() : void
    {
        $attributes = [
            'foo' => $this->faker->randomNumber(),
            'array' => $this->faker->sentences,
            $this->faker->unique()->word => $this->faker->sentence,
        ];
        $uut = $this->makeUUT($attributes);

        $expected = $attributes;
        $expected['foo'] = $attributes['foo'] + 10;

        $cloned = clone($uut);
        static::assertEquals($expected, static::invokeMethod($uut, 'getValidationData'));
        static::assertEquals($cloned, $uut); // Make sure it wasn't modified.
    }

    /**
     * Returns an instance of the class under test.
     *
     * @param mixed ...$arguments
     *
     * @return Model|HasValidation
     */
    public function makeUUT(...$arguments) : object
    {
        $attributes = $arguments[0] ?? [];

        return new class($attributes) extends Model {
            use HasValidation;

            /** @inheritdoc  */
            protected $casts = ['array' => 'array'];

            /** @inheritdoc  */
            protected $guarded = [];

            /** @inheritdoc  */
            protected $hidden = ['foo']; // Visibility shouldn't impact the returned validation data.

            protected function getValidator() : Validator
            {
                return ValidatorFacade::make([], []);
            }

            /** @noinspection PhpMissingDocCommentInspection */
            protected function getFooAttribute($value) : int
            {
                return $value + 10;
            }
        };
    }
}
