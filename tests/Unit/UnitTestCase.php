<?php

namespace DanBoehm\Articulant\Tests\Unit;


use DanBoehm\Articulant\Tests\TestCase;

/**
 * Class UnitTestCase
 *
 * @package Tests\Unit
 */
abstract class UnitTestCase extends TestCase
{
    /**
     * Returns the class under test for this unit test.
     *
     * @return string
     */
    abstract public static function getCUT() : string;

    /**
     * Returns an instance of the class under test.
     *
     * @param mixed ...$arguments
     *
     * @return object
     */
    protected function makeUUT(...$arguments) : object
    {
        $cut = static::getCUT();

        return new $cut(...$arguments);
    }

    /**
     * Invokes the given method with the given parameters on the object, even if it is protected or private.
     *
     * @param        $object
     * @param string $methodName
     * @param array  $parameters
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected static function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}