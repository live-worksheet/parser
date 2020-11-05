<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter;

use LiveWorksheet\Parser\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    public function testGetValues(): void
    {
        $parameter = new Parameter('Foo', 'bar + 1', Parameter::MODE__FRACTION, 5);

        self::assertEquals('Foo', $parameter->getName());

        self::assertEquals('bar + 1', $parameter->getExpression());

        self::assertEquals(Parameter::MODE__FRACTION, $parameter->getMode());

        self::assertEquals(5, $parameter->getPrecision());

        self::assertEquals('Foo = bar + 1 | fraction 5', (string) $parameter);
    }
}
