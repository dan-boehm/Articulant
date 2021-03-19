<?php

namespace DanBoehm\Articulant\Tests\Unit\Concerns;


use DanBoehm\Articulant\Concerns\HasDefaults;
use DanBoehm\Articulant\Tests\Unit\UnitTestCase;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HasDefaultsTest
 *
 * @package Tests\Unit\App\Models\Concerns
 * @see HasDefaults;
 */
class HasDefaultsTest extends UnitTestCase
{
    /**
     * @inheritDoc
     */
    public static function getCUT() : string
    {
        return HasDefaults::class;
    }

    /** @see HasDefaults::getDefaultAttributes() */
    public function test_getDefaultAttributes() : void
    {
        /** @var array $attributes */
        [$attributes, $defaults] = $this->generateDefaults();

        $expected = $defaults;
        $expected[$attributes['array']] = json_encode($defaults[$attributes['array']], JSON_THROW_ON_ERROR);
        $expected[$attributes['assoc']] = json_encode($defaults[$attributes['assoc']],JSON_THROW_ON_ERROR);
        $expected[$attributes['object']] = json_encode($defaults[$attributes['object']], JSON_THROW_ON_ERROR);

        $uut = $this->makeUUT($defaults);

        static::assertEquals($expected, $uut->getDefaultAttributes());
    }

    /** @see HasDefaults::initializeHasDefaults() */
    public function test_initializeHasDefaults() : void
    {
        /** @var array $attributes */
        [$attributes, $defaults] = $this->generateDefaults();

        $expected = $defaults;
        $expected[$attributes['array']] = json_encode($defaults[$attributes['array']], JSON_THROW_ON_ERROR);
        $expected[$attributes['assoc']] = json_encode($defaults[$attributes['assoc']],JSON_THROW_ON_ERROR);
        $expected[$attributes['object']] = json_encode($defaults[$attributes['object']], JSON_THROW_ON_ERROR);

        $uut = $this->makeUUT($defaults);

        static::assertEquals($expected, $uut->getAttributes());
    }

    /** @see HasDefaults::resetToDefault() */
    public function test_resetToDefault() : void
    {
        /** @var array $attributes */
        [$attributes, $defaults] = $this->generateDefaults();

        $expected = $defaults;
        $expected[$attributes['array']] = json_encode($defaults[$attributes['array']], JSON_THROW_ON_ERROR);
        $expected[$attributes['assoc']] = json_encode($defaults[$attributes['assoc']],JSON_THROW_ON_ERROR);
        $expected[$attributes['object']] = json_encode($defaults[$attributes['object']], JSON_THROW_ON_ERROR);

        $uut = $this->makeUUT($defaults);
        foreach($attributes as $attr) {
            $uut->setAttribute($attr, $this->faker->unique()->word);
        }

        $extraAttr = $this->faker->unique()->word;
        $expected[$extraAttr] = $this->faker->unique()->sentence;
        $uut->setAttribute($extraAttr, $expected[$extraAttr]);

        $uut->resetToDefault(true);
        static::assertEquals($expected, $uut->getAttributes());

        unset($expected[$extraAttr]);
        $uut->resetToDefault();

        static::assertEquals($expected, $uut->getAttributes());
    }

    /** @see HasDefaults::isDefault() */
    public function test_isDefault() : void
    {
        /** @var array $attributes */
        [$attributes, $defaults] = $this->generateDefaults();
        $uut = $this->makeUUT($defaults);

        static::assertTrue($uut->isDefault());
        static::assertTrue($uut->isDefault(false));

        foreach($attributes as $attr) {
            $uut->setAttribute($attr, $this->faker->unique()->sentence);

            static::assertFalse($uut->isDefault());
            static::assertFalse($uut->isDefault(false));

            $uut->resetToDefault();
        }

        $uut->setAttribute($this->faker->unique()->word, null);
        static::assertTrue($uut->isDefault());
        static::assertFalse($uut->isDefault(false));
    }

    /**
     * Generates random default attributes for testing.
     *
     * The attributes for each test case are returned as the first element.
     * The defaults are returned as the second element.
     *
     * @return array[][]
     */
    protected function generateDefaults() : array
    {
        $attributes = [
            'null' => $this->faker->unique()->word,
            'string' => $this->faker->unique()->word,
            'int' => $this->faker->unique()->word,
            'float' => $this->faker->unique()->word,
            'date' => $this->faker->unique()->word,
            'datetime' => $this->faker->unique()->word,
            'array' => $this->faker->unique()->word,
            'assoc' => $this->faker->unique()->word,
            'object' => $this->faker->unique()->word,
            'json' => $this->faker->unique()->word,
        ];

        $defaults = [
            $attributes['null'] => null,
            $attributes['string'] => $this->faker->unique()->sentence,
            $attributes['int'] => $this->faker->randomNumber(),
            $attributes['float'] => $this->faker->randomFloat(),
            $attributes['date'] => $this->faker->date(),
            $attributes['datetime'] => $this->faker->dateTime->format(DATE_ATOM),
            $attributes['array'] => $this->faker->words,
            $attributes['assoc'] => array_combine($this->faker->unique()->words, $this->faker->sentences),
            $attributes['object'] => (object) array_combine($this->faker->unique()->words, $this->faker->sentences),
            $attributes['json'] => json_encode($this->faker->words, JSON_THROW_ON_ERROR),
        ];

        return [$attributes, $defaults];
    }

    /** @inheritdoc  */
    protected function makeUUT(...$arguments) : object
    {
        $defaults = $arguments[0] ?? [];
        $attributes = $arguments[1] ?? [];

        return new class($defaults, $attributes) extends Model {
            use HasDefaults;

            protected $defaults;

            public function __construct(array $defaults, array $attributes = [])
            {
                $this->defaults = $defaults;

                parent::__construct($attributes);
            }
        };
    }
}
