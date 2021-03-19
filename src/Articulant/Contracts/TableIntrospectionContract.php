<?php

namespace DanBoehm\Articulant\Contracts;


use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\Connection;

/**
 * Interface TableIntrospectionContract
 *
 * @package DanBoehm\Articulant\Contracts
 */
interface TableIntrospectionContract
{
    /**
     * Returns the name of the database this model's table is in.
     *
     * @return string
     */
    public static function getDatabaseName() : string;

    /**
     * Returns the name of this model's table.
     *
     * @return string
     */
    public static function getTableName() : string;

    /**
     * Returns the name of the connection this model uses.
     *
     * @return string|null
     */
    public static function getDefaultConnectionName() : ?string;

    /**
     * Returns a fully qualified table name (includes database)
     *
     * @return string
     */
    public static function getQualifiedTableName() : string;

    /**
     * Return associative array of column schemas.  Keys will be the column name.
     *
     * @return Column[]
     */
    public static function listTableColumns() : array;

    /**
     * Get the column definition by column name.
     *
     * @param $column
     *
     * @return Column|null
     */
    public static function getTableColumn($column) : ?Column;

    /**
     * Returns all of the table's column names.
     *
     * @return string[]
     */
    public static function getTableColumnNames() : array;

    /**
     * Returns the connection this model uses by default.
     *
     * @return Connection
     */
    public static function getDefaultConnection() : Connection;

    /**
     * Returns the primary key of this model.
     *
     * @return string
     */
    public static function getPrimaryKeyName() : string;

    /**
     * Returns the fully-qualified primary key of this model.
     *
     * @return string
     */
    public static function getQualifiedPrimaryKeyName() : string;

    /**
     * Returns the fully-qualified primary key of this model (including the database).
     *
     * @return string
     */
    public static function getSuperQualifiedPrimaryKeyName() : string;
}
