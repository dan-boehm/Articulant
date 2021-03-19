<?php

namespace DanBoehm\Articulant;


use DanBoehm\Articulant\Concerns\AutomaticCasting;
use DanBoehm\Articulant\Concerns\HasDefaults;
use DanBoehm\Articulant\Concerns\HasValidation;
use DanBoehm\Articulant\Concerns\TableIntrospection;
use DanBoehm\Articulant\Contracts\TableIntrospectionContract;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Class Model
 *
 * Extra Features:
 *     - Connection/Database/Table/Key info are static and there are static methods that retrieve useful information
 *     about the model's table.
 *     - Casting defaults based on the database column types.  This only works for the types supported by Doctrine.
 *     - Makes models validatable and validates them before saving. `Model::save()` returns `false` if validation
 *     fails.
 *
 * @package App\Models
 * @see     TableIntrospection
 * @see     AutomaticCasting
 * @see     HasValidation
 * @see     HasDefaults
 */
abstract class Model extends EloquentModel implements TableIntrospectionContract
{
    use AutomaticCasting;
    use HasDefaults;
    use HasValidation;

    /** @var array $defaults The default attributes for this model. */
    protected $defaults = [];

    /** @inheritdoc */
    public $timestamps = false;

    /** @inheritdoc */
    public static function booted() : void
    {
        static::saving(static function(self $model) : ?bool {
            return $model->validate() ? null : false;
        });

        static::saved(static function(self $model) : void {
            $model->refresh();
        });
    }

    /**
     * Returns true if this model uses SoftDeletes.
     *
     * @return bool
     * @see SoftDeletes
     */
    public static function usesSoftDeletes() : bool
    {
        return static::hasGlobalScope(new SoftDeletingScope());
    }
}
