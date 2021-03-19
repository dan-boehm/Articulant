<?php

namespace DanBoehm\Articulant\Tests\Unit\Concerns;


use DanBoehm\Articulant\Concerns\TableIntrospection;
use DanBoehm\Articulant\Tests\Concerns\WithMemoryDB;
use DanBoehm\Articulant\Tests\Unit\UnitTestCase;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class TableIntrospectionTest
 *
 * @package Tests\Unit\App\Models\Concerns
 * @see TableIntrospection
 */
class TableIntrospectionTest extends UnitTestCase
{
    use WithMemoryDB;

    public static function getCUT() : string
    {
        return TableIntrospection::class;
    }

    /** @see TableIntrospection::getTableName() */
    public function test_getTableName() : void
    {
        // Default Case
        $uut = $this->makeUUT();
        $expected = $uut->setTable(null)->getTable();
        static::assertSame($expected, $uut::getTableName());

        $expected = $this->faker->word;
        $uut = $this->makeUUT(['tableName' => $expected]);

        static::assertSame($expected, $uut::getTableName());
    }

    /** @see TableIntrospection::getDefaultConnectionName() */
    public function test_getDefaultConnectionName() : void
    {
        // Default Case
        $uut = $this->makeUUT();
        static::assertNull($uut::getDefaultConnectionName());

        $expected = $this->faker->word;
        $uut = $this->makeUUT(['connectionName' => $expected]);

        static::assertSame($expected, $uut::getDefaultConnectionName());
    }

    /** @see TableIntrospection::getTableColumn() */
    public function test_getTableColumn() : void
    {
        $columns = $this->createTestTable('test_getTableColumn');
        $uut = $this->makeUUT(['tableName' => 'test_getTableColumn']);

        foreach($columns as $name) {
            $expected = DB::getDoctrineSchemaManager()->listTableColumns('test_getTableColumn')[$name];
            $actual = $uut::getTableColumn($name);

            static::assertInstanceOf(Column::class, $actual);
            static::assertEquals($expected, $actual);
        }
    }

    /** @see TableIntrospection::getDefaultConnection() */
    public function test_getDefaultConnection() : void
    {
        // Default Case
        $uut = $this->makeUUT();
        static::assertSame(DB::connection(), $uut::getDefaultConnection());

        // Explicit Case
        $name = $this->faker->word;
        $uut = $this->makeUUT(['connectionName' => $name]);

        static::assertSame(DB::connection($name), $uut::getDefaultConnection());
    }

    /** @see TableIntrospection::initializeTableIntrospection() */
    public function test_initializeTableIntrospection() : void
    {
        // No database name
        $expected = [
            'connectionName' => $this->faker->word,
            'tableName' => $this->faker->word,
            'primaryKeyName' => $this->faker->word,
        ];

        $uut = $this->makeUUT($expected);
        $uut->initializeTableIntrospection();

        static::assertSame($expected['connectionName'], $uut->getConnection()->getName());
        static::assertSame($expected['tableName'], $uut->getTable());
        static::assertSame($expected['primaryKeyName'], $uut->getKeyName());

        // No database name
        $expected['databaseName'] = $this->faker->word;

        $uut = $this->makeUUT($expected);
        $uut->initializeTableIntrospection();

        static::assertSame($expected['connectionName'], $uut->getConnection()->getName());
        static::assertSame("{$expected['databaseName']}.{$expected['tableName']}", $uut->getTable());
        static::assertSame($expected['primaryKeyName'], $uut->getKeyName());
    }

    /** @see TableIntrospection::getQualifiedTableName() */
    public function test_getQualifiedTableName() : void
    {
        $tableName = $this->faker->word;

        $uut = $this->makeUUT(['tableName' => $tableName]);

        $expected = DB::connection()->getDatabaseName() . ".$tableName";

        static::assertSame($expected, $uut::getQualifiedTableName());
    }

    /** @see TableIntrospection::listTableColumns() */
    public function test_listTableColumns() : void
    {
        $columns = $this->createTestTable('test_listTableColumns');
        $uut = $this->makeUUT(['tableName' => 'test_listTableColumns']);

        $actual = $uut::listTableColumns();
        static::assertCount(count($columns), $actual);
        foreach($columns as $column) {
            static::assertArrayHasKey($column, $actual);
            static::assertInstanceOf(Column::class, $actual[$column]);
        }
    }

    /** @see TableIntrospection::getTableColumnNames() */
    public function test_getTableColumnNames() : void
    {
        $columns = $this->createTestTable('test_getTableColumnNames');
        $uut = $this->makeUUT(['tableName' => 'test_getTableColumnNames']);

        $actual = $uut::getTableColumnNames();
        static::assertEqualsCanonicalizing($columns, $actual);
    }

    /** @see TableIntrospection::getDatabaseName() */
    public function test_getDatabaseName() : void
    {
        // Default Case
        $uut = $this->makeUUT();
        static::assertSame(DB::connection()->getDatabaseName(), $uut::getDatabaseName());

        // ExplicitCase
        $expected = $this->faker->word;
        $uut = $this->makeUUT(['databaseName' => $expected]);

        static::assertSame($expected, $uut::getDatabaseName());
    }

    /** @see TableIntrospection::getPrimaryKeyName() */
    public function test_getPrimaryKeyName() : void
    {
        // Default Case
        $uut = $this->makeUUT();
        static::assertSame('id', $uut::getPrimaryKeyName());

        // ExplicitCase
        $expected = $this->faker->word;
        $uut = $this->makeUUT(['primaryKeyName' => $expected]);

        static::assertSame($expected, $uut::getPrimaryKeyName());
    }

    /** @see TableIntrospection::getSuperQualifiedPrimaryKeyName() */
    public function test_getSuperQualifiedPrimaryKeyName() : void
    {
        $keyName = $this->faker->word;
        $tableName = $this->faker->word;
        $dbName = $this->faker->word;
        $uut = $this->makeUUT(['databaseName' => $dbName, 'tableName' => $tableName, 'primaryKeyName' => $keyName]);

        static::assertSame("$dbName.$tableName.$keyName", $uut::getSuperQualifiedPrimaryKeyName());
    }

    /** @see TableIntrospection::getQualifiedPrimaryKeyName() */
    public function test_getQualifiedPrimaryKeyName() : void
    {
        $keyName = $this->faker->word;
        $tableName = $this->faker->word;
        $dbName = $this->faker->word;
        $uut = $this->makeUUT(['databaseName' => $dbName, 'tableName' => $tableName, 'primaryKeyName' => $keyName]);

        static::assertSame("$tableName.$keyName", $uut::getQualifiedPrimaryKeyName());
    }

    /** @inheritdoc */
    protected function makeUUT(...$arguments) : object
    {
        $staticVars = $arguments[0] ?? [];

        return new class($staticVars, [$this, 'configureMemoryDB']) extends Model {
            use TableIntrospection;

            protected static $connectionName;
            protected static $databaseName;
            protected static $tableName;
            protected static $primaryKeyName;

            public function __construct(array $staticVars = [], callable $connectionCallback = null)
            {
                self::purgeTableIntrospectionColumnCache();
                if ($connection = $staticVars['connectionName'] ?? null) {
                    $connectionCallback($connection);
                }

                self::$connectionName = $staticVars['connectionName'] ?? null;
                self::$databaseName = $staticVars['databaseName'] ?? null;
                self::$tableName = $staticVars['tableName'] ?? null;
                self::$primaryKeyName = $staticVars['primaryKeyName'] ?? null;

                parent::__construct();
            }
        };
    }

    /**
     * Creates a test table on the default connection's current database.
     *
     * @param string $name
     *
     * @return array
     */
    protected function createTestTable(string $name) : array
    {
        $columns = [ // `$this->faker->unique()->words` doesn't guarantee that all words will be unique.
            $this->faker->unique()->word,
            $this->faker->unique()->word,
            $this->faker->unique()->word,
        ];

        Schema::create($name, static function(Blueprint $table) use ($columns) : void {
            foreach($columns as $name) {
                $table->string($name);
            }
        });

        return $columns;
    }
}
