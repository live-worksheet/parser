<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter\ExpressionLanguage\SequentialRandom;

use LiveWorksheet\Parser\Parameter\ExpressionLanguage\SequentialRandom\IntegerGenerator;
use PHPUnit\Framework\TestCase;

class IntegerGeneratorTest extends TestCase
{
    public function testGeneratesRandomNumbers(): void
    {
        $generator1 = new IntegerGenerator(1);

        self::assertEquals(5, $generator1->next(5, 5));
        self::assertEquals(-15, $generator1->next(-20, -5));
        self::assertEquals(72, $generator1->next(0, 100));

        // Results should be different with other specimen
        $generator2 = new IntegerGenerator(1, 'foo');

        self::assertEquals(5, $generator2->next(5, 5));
        self::assertEquals(-13, $generator2->next(-20, -5));
        self::assertEquals(31, $generator2->next(0, 100));
    }

    public function testGeneratesSequentialRandomNumbers(): void
    {
        $generator1 = new IntegerGenerator(123, 'foo');
        $generator2 = new IntegerGenerator(123, 'foo');

        for ($i = 0; $i < 20; ++$i) {
            self::assertEquals($generator1->next(0, 100), $generator2->next(0, 100));
        }
    }
}
