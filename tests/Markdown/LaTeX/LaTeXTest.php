<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown\LaTeX;

use LiveWorksheet\Parser\Markdown\LaTeX\LaTeX;
use PHPUnit\Framework\TestCase;

class LaTeXTest extends TestCase
{
    public function testIsContainer(): void
    {
        $latex = new LaTeX();

        self::assertTrue($latex->isContainer());
    }
}
