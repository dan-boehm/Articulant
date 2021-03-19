<?php /** @noinspection PhpUndefinedFieldInspection */  // TODO: remove this when this ticket gets resolved: https://youtrack.jetbrains.com/issue/WI-47833

namespace DanBoehm\Articulant\Concerns;


use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Trait AutomaticCasting
 *
 * This trait can be configured via a config file in the `autocasting` domain.
 *
 * The default is:
 *
 * ```
 * return [
 *     'arrays' => 'array', // 'array' or 'object'
 *     'json' => 'array',   // 'array' or' object'
 *
 *      // See https://www.php.net/manual/en/datetime.format.php
 *     'formats' => [
 *         'datetime' => DATE_ATOM,
 *         'date' => 'Y-m-d',
 *         'time' => 'H:i:s',
 *     ],
 * ];
 * ```
 *
 * Individual models can be further configured via several static properties.
 *
 * ```
 * protected static string $autocastArrayTo;
 * protected static string $autocastJsonTo;
 * protected static string $autocastDatetimeFormat;
 * protected static string $autocastDateFormat;
 * protected static string $autocastTimeFormat;
 * ```
 *
 * This trait includes the TableIntrospection trait.
 *
 * @package DanBoehm\Articulant\Concerns
 * @mixin EloquentModel
 * @see TableIntrospection
 */
trait AutomaticCasting
{
    use TableIntrospection;

    /** @var array Cache for the auto-casts. */
    protected static $_automaticCasting_autoCasts = [];

    /**
     * Boot the AutomaticCasting trait.
     */
    public static function bootAutomaticCasting() : void
    {
        $casts = [];
        $columns = static::listTableColumns();
        $types = array_flip(Type::getTypesMap());
        foreach($columns as $column) {
            $name = $column->getName();
            $type = $types[get_class($column->getType())] ?? null;
            switch($type) {
                case Types::ARRAY:
                case Types::SIMPLE_ARRAY:
                    $casts[$name] = static::$autocastArrayTo ?? config('autocasting.arrays', 'array');
                    break;
                case Types::JSON:
                    $casts[$name] = static::$autocastJsonTo ?? config('autocasting.json', 'array');
                    break;
                case Types::BIGINT:
                case Types::INTEGER:
                case Types::SMALLINT:
                    $casts[$name] = 'integer';
                    break;
                case Types::BOOLEAN:
                    $casts[$name] = 'bool';
                    break;
                case Types::DATETIME_MUTABLE:
                case Types::DATETIMETZ_MUTABLE:
                case Types::DATETIMETZ_IMMUTABLE:
                    $casts[$name] = 'datetime:' . (static::$autocastDatetimeFormat ?? config('autocasting.formats.datetime', DATE_ATOM));
                    break;
                case Types::TIME_MUTABLE:
                case Types::TIME_IMMUTABLE:
                    $casts[$name] = "datetime:" . (static::$autocastTimeFormat ?? config('autocasting.formats.time', 'H:i:s'));
                    break;
                case Types::DATE_MUTABLE:
                case Types::DATE_IMMUTABLE:
                    $casts[$name] = "date:" . (static::$autocastDateFormat ?? config('autocasting.formats.date', 'Y-m-d'));
                    break;
                case Types::DECIMAL:
                case Types::FLOAT:
                    $casts[$name] = 'float';
                    break;
            }
        }

        static::$_automaticCasting_autoCasts[static::class] = $casts;
    }

    /**
     * @see HasAttributes::getCasts()
     *
     * @return array
     */
    public function getCasts() : array
    {
        return array_merge(static::$_automaticCasting_autoCasts[static::class], parent::getCasts());
    }
}
