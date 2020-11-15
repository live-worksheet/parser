<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter;

use LiveWorksheet\Parser\Parameter\ParameterTestContext;
use LiveWorksheet\Parser\Parameter\Types\StaticType;
use PHPUnit\Framework\TestCase;

class ParameterTestContextTest extends TestCase
{
    public function testGetProperties(): void
    {
        $parameter1 = new StaticType('foo', 'A');
        $parameter2 = new StaticType('bar', 'B', 'C');

        $context = new ParameterTestContext(['foo' => $parameter1, 'bar' => $parameter2]);

        self::assertEquals(1, $context->getSeed());

        self::assertNull($context->getUserInput('foo'));
        self::assertNull($context->getUserInput('bar'));

        self::assertEquals($parameter1, $context->getParameter('foo'));
        self::assertEquals($parameter2, $context->getParameter('bar'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Parameter 'foobar' was not expected to be requested.");

        $context->getParameter('foobar');
    }
}
