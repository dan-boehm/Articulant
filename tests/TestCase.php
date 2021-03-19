<?php

namespace DanBoehm\Articulant\Tests;

use DanBoehm\Articulant\ArticulantServiceProvider;
use Faker\Factory as FakerFactory;
use Faker\Generator as Faker;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * Class TestCase
 *
 * @package Tests
 */
abstract class TestCase extends OrchestraTestCase
{
    /** @var Faker */
    protected $faker;

    protected function setUp() : void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        /** @var string[] $called */
        $called = [];
        foreach(class_uses_recursive(static::class) as $trait) {
            $method = 'setUpBeforeClass' . class_basename($trait);
            if (method_exists(static::class, $method) && !in_array($method, $called)) {
                static::{$method}();

                $called[] = $method;
            }
        }
    }

    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();

        /** @var string[] $called */
        $called = [];
        foreach(class_uses_recursive(static::class) as $trait) {
            $method = 'tearDownAfterClass' . class_basename($trait);
            if (method_exists(static::class, $method) && !in_array($method, $called)) {
                static::{$method}();

                $called[] = $method;
            }
        }
    }

    protected function tearDown() : void
    {
        $this->tearDownTraits();

        parent::tearDown();
    }

    /** @inheritdoc  */
    protected function setUpTraits() : array
    {
        $except = [];

        $uses = parent::setUpTraits();

        /** @var string[] $called */
        $called = [];
        foreach(array_diff(array_keys($uses), $except) as $trait) {
            $method = 'setUp' . class_basename($trait);
            if (method_exists(static::class, $method) && !in_array($method, $called)) {
                $this->{$method}();

                $called[] = $method;
            }
        }

        return $uses;
    }

    /**
     * Calls the teardown methods for all of this class's traits.
     */
    protected function tearDownTraits() : void
    {
        /** @var string[] $called */
        $called = [];
        foreach(class_uses_recursive(static::class) as $trait) {
            $method = 'tearDown' . class_basename($trait);
            if (method_exists(static::class, $method) && !in_array($method, $called)) {
                $this->{$method}();

                $called[] = $method;
            }
        }
    }

    /** @inheritDoc */
    protected function getPackageProviders($app) : array
    {
        return [ArticulantServiceProvider::class];
    }

    /** @inheritDoc */
    protected function getEnvironmentSetUp($app) : void
    {
        // Perform Environment Setup
    }
}