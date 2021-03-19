<?php

namespace DanBoehm\Articulant\Tests\Concerns;


/**
 * Trait WithMemoryDB
 *
 * Adds the ability to configure and interact with in-memory databases for testing.
 */
trait WithMemoryDB
{
    protected $connectionName = 'test_db';

    protected $defaultConnectionName;

    protected $configuredConnections = [];

    /**
     * Setup for this trait.
     */
    public function setUpWithMemoryDB() : void
    {
        $this->defaultConnectionName = config('database.default');
        $this->configureMemoryDB($this->connectionName);
        config(['database.default' => $this->connectionName]);
    }

    /**
     * Teardown for this trait.
     */
    public function tearDownWithMemoryDB() : void
    {
        $connections = config('database.connections');
        foreach($this->configuredConnections as $del) {
            unset($connections[$del]);
        }
        config([
                   'database.connections' => $connections,
                   'database.default'     => $this->defaultConnectionName,
               ]);
    }

    /**
     * Configures an in-memory DB
     *
     * @param $name
     */
    public function configureMemoryDB($name) : void
    {
        if (config("database.connections.name")) {
            throw new \RuntimeException("A connection with this name already exists.");
        }

        config([
                   "database.connections.$name" => [
                       'driver'   => 'sqlite',
                       'database' => ':memory:',
                   ],
               ]);

        $this->configuredConnections[] = $name;
    }
}
