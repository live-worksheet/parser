<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter\ExpressionLanguage;

use LiveWorksheet\Parser\Parameter\ExpressionLanguage\VariableExtractor;
use PHPUnit\Framework\TestCase;

class VariableExtractorTest extends TestCase
{
    /**
     * @dataProvider provideExpressions
     */
    public function testFindsVariables(string $expression, array $variables): void
    {
        self::assertEquals(VariableExtractor::getVariables($expression), $variables);
    }

    public function provideExpressions(): \Generator
    {
        yield 'none' => [
            '1 + 2 + 3', [],
        ];

        yield 'single' => [
            '1 + Foo', ['Foo'],
        ];

        yield 'multiple' => [
            '(1 - Foo) + FooBar', ['Foo', 'FooBar'],
        ];

        yield 'repeating' => [
            'A + B + A + B', ['A', 'B'],
        ];

        yield 'ignoring functions and constants' => [
            'false or FALSE or true or TRUE or null or Foo or Bar()', ['Foo'],
        ];

        yield 'empty result if invalid expression' => [
            'this = is invalid', [],
        ];
    }
}
