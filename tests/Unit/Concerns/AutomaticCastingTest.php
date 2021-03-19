<?php

namespace DanBoehm\Articulant\Tests\Unit\Concerns;


use DanBoehm\Articulant\Concerns\AutomaticCasting;
use DanBoehm\Articulant\Tests\Unit\UnitTestCase;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\ArrayType;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzImmutableType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\SimpleArrayType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\TimeImmutableType;
use Doctrine\DBAL\Types\TimeType;
use Doctrine\DBAL\Types\Type;
use Faker\Generator;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AutomaticCastingTest
 *
 * @package Tests\Unit\App\Models\Concerns
 */
class AutomaticCastingTest extends UnitTestCase
{
    protected const ARRAY_TYPES = [
        ArrayType::class,
        SimpleArrayType::class,
    ];

    protected const JSON_TYPES = [
        JsonType::class,
    ];

    protected const INT_TYPES = [
        BigIntType::class,
        IntegerType::class,
        SmallIntType::class,
    ];

    protected const BOOL_TYPES = [
        BooleanType::class,
    ];

    protected const DATETIME_TYPES = [
        DateTimeType::class,
        DateTimeTzType::class,
        DateTimeTzImmutableType::class,
    ];

    protected const TIME_TYPES = [
        TimeType::class,
        TimeImmutableType::class,
    ];

    protected const DATE_TYPES = [
        DateType::class,
        DateImmutableType::class,
    ];

    protected const FLOAT_TYPES = [
        DecimalType::class,
        FloatType::class,
    ];

    /** @inheritdoc  */
    public static function getCUT() : string
    {
        return AutomaticCasting::class;
    }

    /** @see AutomaticCasting::bootAutomaticCasting() */
    public function test_bootAutomaticCasting() : void
    {
        // Config Case
        $config = ['arrays' => $this->faker->word, 'json' => $this->faker->word, 'formats' => [
            'datetime' => $this->faker->word,
            'date' => $this->faker->word,
            'time' => $this->faker->word,
        ]];

        $oldConfig = config('autocasting');
        config(['autocasting' => $config]);

        $uut = $this->makeUUT();

        config(['autocasting' => $oldConfig]);

        $expected = $this->getExpectedAutoCasts($uut::listTableColumns(), $config);
        $actual = $uut->getCasts();

        static::assertEquals($expected, $actual);

        // Override Case
        $overrides = [
            'autocastArrayTo' => $this->faker->word,
            'autocastJsonTo' => $this->faker->word,
            'autocastDatetimeFormat' => $this->faker->word,
            'autocastDateFormat' => $this->faker->word,
            'autocastTimeFormat' => $this->faker->word,
        ];
        $uut = $this->makeUUT($overrides);

        $expected = $this->getExpectedAutoCasts($uut::listTableColumns(), null, $overrides);
        $actual = $uut->getCasts();

        static::assertEquals($expected, $actual);
    }

    /** @see AutomaticCasting::getCasts() */
    public function test_getCasts() : void
    {
        // Default case is tested by the boot test.

        // Test merge
        $uut = $this->makeUUT();

        $columns = $uut::listTableColumns();
        $autoCasts = $this->getExpectedAutoCasts($columns);
        $casts = [
            $this->faker->unique()->word => 'bool',
            $this->faker->unique()->word => 'integer',
            $this->faker->unique()->word => 'something non-sensible',
        ];

        /** @var Column $column */
        foreach ($this->faker->randomElements($columns) as $column) {
            $casts[$column->getName()] = $this->faker->word;
        }

        $uut->setCasts($casts);

        $expected = array_merge($autoCasts, $casts);

        static::assertEquals($expected, $uut->getCasts());
    }

    /**
     * @inheritdoc
     */
    protected function makeUUT(...$arguments) : object
    {
        $staticVars = $arguments[0] ?? [];

        return new class($this->faker, $staticVars) extends Model {
            use AutomaticCasting {
                AutomaticCasting::listTableColumns as __listTableColumns;
            }

            private static $_columns;

            /** @inheritdoc  */
            public $incrementing = false;

            protected static $autocastArrayTo;
            protected static $autocastJsonTo;
            protected static $autocastDatetimeFormat;
            protected static $autocastDateFormat;
            protected static $autocastTimeFormat;

            public function __construct(Generator $faker, array $staticVars = [])
            {
                $this->generateColumns($faker);

                static::$autocastArrayTo = $staticVars['autocastArrayTo'] ?? null;
                static::$autocastJsonTo = $staticVars['autocastJsonTo'] ?? null;
                static::$autocastDatetimeFormat = $staticVars['autocastDatetimeFormat'] ?? null;
                static::$autocastDateFormat = $staticVars['autocastDateFormat'] ?? null;
                static::$autocastTimeFormat = $staticVars['autocastTimeFormat'] ?? null;

                static::bootAutomaticCasting();

                parent::__construct();
            }

            private function generateColumns(Generator $faker) : void
            {
                $types = array_values(Type::getTypesMap());

                self::$_columns = array_map(static function(string $typeClass) use ($faker) : Column {
                    return new Column($faker->unique()->word, new $typeClass);
                }, $types);
            }

            /**
             * @return Column[]
             */
            public static function listTableColumns() : array
            {
                // Return an array with one of each columns type.
                return self::$_columns;
            }

            public function setCasts(array $casts) : void
            {
                $this->casts = $casts;
            }
        };
    }

    /**
     * Returns the expected auto-casts.
     *
     * @param Column[]   $columns
     * @param array|null $config
     * @param array      $staticVars
     *
     * @return string[]
     */
    protected function getExpectedAutoCasts(array $columns, array $config = null, array $staticVars = []) : array
    {
        if ($config === null) {
            $config = config('articulant.autocasting');
        }

        $datetimeFormat = $staticVars['autocastDatetimeFormat'] ?? $config['formats']['datetime'];
        $dateFormat = $staticVars['autocastDateFormat'] ?? $config['formats']['date'];
        $timeFormat = $staticVars['autocastTimeFormat'] ?? $config['formats']['time'];

        $expected = [];
        foreach($columns as $column) {
            $class = get_class($column->getType());
            $cast = null;
            if (in_array($class, self::ARRAY_TYPES, true)) {
                $cast = $staticVars['autocastArrayTo'] ?? $config['arrays'];
            } else if (in_array($class, self::JSON_TYPES, true)) {
                $cast = $staticVars['autocastJsonTo'] ?? $config['json'];
            } else if (in_array($class, self::INT_TYPES, true)) {
                $cast = 'integer';
            } else if (in_array($class, self::BOOL_TYPES, true)) {
                $cast = 'bool';
            } else if (in_array($class, self::DATETIME_TYPES, true)) {
                $cast = 'datetime:' . $datetimeFormat;
            } else if (in_array($class, self::DATE_TYPES, true)) {
                $cast = 'date:' . $dateFormat;
            } else if (in_array($class, self::TIME_TYPES, true)) {
                $cast = 'datetime:' . $timeFormat;
            } else if (in_array($class, self::FLOAT_TYPES, true)) {
                $cast = 'float';
            }

            if ($cast !== null) {
                $expected[$column->getName()] = $cast;
            }
        }

        return $expected;
    }
}
