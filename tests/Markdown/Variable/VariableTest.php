<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown\Variable;

use LiveWorksheet\Parser\Markdown\Variable\Variable;
use PHPUnit\Framework\TestCase;

class VariableTest extends TestCase
{
    public function testGetProperties(): void
    {
        $a = new Variable('A', true);
        $b = new Variable('B', false);

        self::assertEquals('A', $a->getName());
        self::assertTrue($a->isInput());

        self::assertEquals('B', $b->getName());
        self::assertFalse($b->isInput());
    }
}
