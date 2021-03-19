<?php

namespace DanBoehm\Articulant\Tests\Unit;


/**
 * Class AbstractUnitTestCase
 *
 * @package Tests\Unit
 */
abstract class AbstractUnitTestCase extends UnitTestCase
{
    /** @inheritdoc  */
    protected function makeUUT(...$arguments) : object
    {
        return $this->getMockForAbstractClass(static::getCUT(), $arguments);
    }
}