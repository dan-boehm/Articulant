<?php /** @noinspection PhpUndefinedFieldInspection */  // TODO: remove this when this ticket gets resolved: https://youtrack.jetbrains.com/issue/WI-47833

namespace DanBoehm\Articulant\Concerns;


use DanBoehm\Articulant\Contracts\TableIntrospectionContract;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Trait TableIntrospection
 *
 * This trait can be configured via several static properties that may be set on the model.
 *
 * ```
 * protected static ?string $connectionName; // Works like $this->connection
 * protected static ?string $databaseName; // No Eloquent equivalent
 * protected static ?string $tableName; // Works like $this->table
 * protected static ?string $primaryKeyName; // Works like $this->primaryKey
 * ```
 *
 * Their defaults are all consistent with the behavior of their Eloquent counter-parts.  `$databaseName` defaults to
 * the connection's default database.
 *
 * @package DanBoehm\Articulant\Concerns
 * @see     TableIntrospectionContract
 *
 * @mixin Model
 */
trait TableIntrospection
{
    /**
     * The column definitions for this model's table.
     *
     * The columns are keyed by class.  This is because of how trait inheritance works.
     * See:
     * https://stackoverflow.com/questions/48466038/how-static-variables-in-traits-dont-lose-its-value-when-used-inside-class-in-ph
     *
     * @var Column[][]
     */
    protected static $_tableIntrospection_tableColumns = [];

    /**
     * Initiate the TableIntrospection trait.
     */
    public function initializeTableIntrospection() : void
    {
        $connectionDB = static::getDefaultConnection()->getDatabaseName();

        $this->connection = static::getDefaultConnectionName();
        $this->table = $connectionDB === static::getDatabaseName() ? static::getTableName() : static::getQualifiedTableName();
        $this->primaryKey = static::getPrimaryKeyName();
    }

    /**
     * @return string|null
     * @see TableIntrospectionContract::getDefaultConnectionName()
     */
    public static function getDefaultConnectionName() : ?string
    {
        return static::$connectionName ?? null;
    }

    /**
     * @return string
     * @see TableIntrospectionContract::getDatabaseName()
     */
    public static function getDatabaseName() : string
    {
        return static::$databaseName ?? static::getDefaultConnection()->getDatabaseName();
    }

    /**
     * @return string
     * @see TableIntrospectionContract::getTableName()
     */
    public static function getTableName() : string
    {
        return static::$tableName ?? Str::snake(Str::pluralStudly(class_basename(static::class)));
    }

    /**
     * @return string
     * @see TableIntrospectionContract::getQualifiedTableName()
     */
    public static function getQualifiedTableName() : string
    {
        return static::getDatabaseName() . '.' . static::getTableName();
    }

    /**
     * @return string
     * @see TableIntrospectionContract::getPrimaryKeyName()
     */
    public static function getPrimaryKeyName() : string
    {
        return static::$primaryKeyName ?? 'id';
    }

    /**
     * @return string
     * @see TableIntrospectionContract::getQualifiedPrimaryKeyName()
     */
    public static function getQualifiedPrimaryKeyName() : string
    {
        return static::getTableName() . '.' . static::getPrimaryKeyName();
    }

    /**
     * @return string
     * @see TableIntrospectionContract::getSuperQualifiedPrimaryKeyName()
     */
    public static function getSuperQualifiedPrimaryKeyName() : string
    {
        return static::getQualifiedTableName() . '.' . static::getPrimaryKeyName();
    }

    /**
     * @return Column[]
     * @see TableIntrospectionContract::listTableColumns()
     */
    public static function listTableColumns() : array
    {
        if (empty(static::$_tableIntrospection_tableColumns[static::class])) {
            $conn = static::getDefaultConnection();

            // If the PDO connections don't exist, the doctrine connection will fail.
            if ($conn->getPdo() === null || $conn->getReadPdo() === null) {
                $conn->reconnect();
            }

            static::$_tableIntrospection_tableColumns[static::class] = $conn->getDoctrineSchemaManager()
                                                                            ->listTableColumns(static::getTableName(),
                                                                                               static::getDatabaseName());
        }

        return static::$_tableIntrospection_tableColumns[static::class];
    }

    /**
     * Get the column definition by column name.
     *
     * @param $column
     *
     * @return Column|null
     */
    public static function getTableColumn($column) : ?Column
    {
        return static::listTableColumns()[$column] ?? null;
    }

    /**
     * @return string[]
     * @see TableIntrospectionContract::getTableColumnNames()
     */
    public static function getTableColumnNames() : array
    {
        return array_keys(static::listTableColumns());
    }

    /**
     * @return Connection
     * @see TableIntrospectionContract::getDefaultConnection()
     */
    public static function getDefaultConnection() : Connection
    {
        return static::resolveConnection(static::getDefaultConnectionName());
    }

    /**
     * Clears the column cache for this class.
     *
     * This is used by the unit test.
     */
    private static function purgeTableIntrospectionColumnCache() : void
    {
        unset(static::$_tableIntrospection_tableColumns[static::class]);
    }
}
