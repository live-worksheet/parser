<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Sheet;

use LiveWorksheet\Parser\Sheet\Sheet;
use PHPUnit\Framework\TestCase;

class SheetTest extends TestCase
{
    public function testGetParts(): void
    {
        $sheet = new Sheet(
            'Category/Subcategory/Sheet',
            'content',
            ['foo' => 'bar'],
            ['A' => 'foobar'],
        );

        self::assertEquals('Category/Subcategory/Sheet', $sheet->getFullName());

        self::assertEquals('Sheet', $sheet->getName());

        self::assertEquals('Category/Subcategory', $sheet->getPath());

        self::assertEquals(['foo' => 'bar'], $sheet->getResources());

        self::assertEquals(['A' => 'foobar'], $sheet->getParameters());

        self::assertEquals('Category/Subcategory/Sheet', (string) $sheet);
    }
}
