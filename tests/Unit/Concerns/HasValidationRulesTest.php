<?php

namespace DanBoehm\Articulant\Tests\Unit\Concerns;


use DanBoehm\Articulant\Concerns\HasValidation;
use DanBoehm\Articulant\Concerns\HasValidationRules;
use DanBoehm\Articulant\Model;
use DanBoehm\Articulant\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class HasValidationTest
 *
 * @package Tests\Unit\App\Models\Concerns
 * @see HasValidation
 *
 * @method Model|HasValidationRules makeUUT()
 */
class HasValidationRulesTest extends UnitTestCase
{
    /** @inheritdoc */
    public static function getCUT() : string
    {
        return HasValidationRules::class;
    }

    /** @see HasValidationRules::getValidator() */
    public function test_getValidator() : void
    {
        $rules = [
            $this->faker->unique()->word => [$this->faker->words],
            $this->faker->unique()->word => [$this->faker->words],
            $this->faker->unique()->word => [$this->faker->words],
        ];
        $data = array_combine($this->faker->words, $this->faker->sentences);

        /** @var HasValidationRules|Model|MockObject $uut */
        $uut = $this->getMockForTrait(static::getCUT());
        $uut->method('getRules')->willReturn($rules);
        $uut->method('getValidationData')->willReturn($data);

        $actual = $uut->getValidator();

        static::assertEquals($rules, $actual->getRules());
        static::assertEquals($data, $actual->getData());
    }
}
