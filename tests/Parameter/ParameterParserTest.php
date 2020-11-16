<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter;

use LiveWorksheet\Parser\Exception\ParserException;
use LiveWorksheet\Parser\Parameter\ParameterParser;
use LiveWorksheet\Parser\Parameter\Types\FunctionExpressionType;
use LiveWorksheet\Parser\Parameter\Types\StaticType;
use PHPUnit\Framework\TestCase;

class ParameterParserTest extends TestCase
{
    public function testGetRawStructure(): void
    {
        $content = "_functions:\n  - A = 1\n  - B = 2\n\nFoo: Bar";

        $expected = [
            '_functions' => [
                'A = 1',
                'B = 2',
            ],
            'Foo' => 'Bar',
        ];

        $result = $this->getParser()->getRawStructure($content);

        self::assertEquals($expected, $result);
    }

    public function testGetInvalidRawStructure(): void
    {
        $parser = $this->getParser();

        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Unable to parse at line 1 (near "  - X").');

        $parser->getRawStructure('  - X');
    }

    public function testParseStructure(): void
    {
        $parser = $this->getParser();

        $structure = [
            '_functions' => [
                'A = 1',
                [
                    'expr' => 'B = 2.234',
                    'compare' => 'round',
                    'precision' => 2,
                ],
                'FooBar = 2 * (A + B)',
            ],
            'Foo' => 'Some thing',
            'Bar' => [
                'This',
                'That',
            ],
        ];

        $result = $parser->parseStructure($structure);

        self::assertCount(5, $result);

        $parameterA = $result['A'];
        $parameterB = $result['B'];
        $parameterFooBar = $result['FooBar'];
        $parameterFoo = $result['Foo'];
        $parameterBar = $result['Bar'];

        self::assertInstanceOf(FunctionExpressionType::class, $parameterA);
        self::assertEquals('A', $parameterA->getName());
        self::assertEquals('1', $parameterA->getExpression());
        self::assertEquals('exact', $parameterA->getCompareMode());
        self::assertEquals(0, $parameterA->getPrecision());

        self::assertInstanceOf(FunctionExpressionType::class, $parameterB);
        self::assertEquals('B', $parameterB->getName());
        self::assertEquals('2.234', $parameterB->getExpression());
        self::assertEquals('round', $parameterB->getCompareMode());
        self::assertEquals(2, $parameterB->getPrecision());

        self::assertInstanceOf(FunctionExpressionType::class, $parameterFooBar);
        self::assertEquals('FooBar', $parameterFooBar->getName());
        self::assertEquals('2 * (A + B)', $parameterFooBar->getExpression());
        self::assertEquals('exact', $parameterFooBar->getCompareMode());
        self::assertEquals(0, $parameterFooBar->getPrecision());

        self::assertInstanceOf(StaticType::class, $parameterFoo);
        self::assertEquals('Foo', $parameterFoo->getName());
        self::assertEquals(['Some thing'], $parameterFoo->getOptions());

        self::assertInstanceOf(StaticType::class, $parameterBar);
        self::assertEquals('Bar', $parameterBar->getName());
        self::assertEquals(['This', 'That'], $parameterBar->getOptions());
    }

    public function testParseEmptyStructure(): void
    {
        self::assertEmpty($this->getParser()->parseStructure([]));
    }

    /**
     * @dataProvider provideInvalidFunctions
     */
    public function testParseStructureFailsWithInvalidStructure(array $structure, string $expectedException): void
    {
        $parser = $this->getParser();

        $this->expectException(ParserException::class);
        $this->expectExceptionMessage($expectedException);

        $parser->parseStructure($structure);
    }

    public function provideInvalidFunctions(): \Generator
    {
        yield 'empty functions list' => [
            [
                '_functions' => [
                ],
            ],
            'The path "_functions" should have at least 1 element(s) defined.',
        ];

        yield 'explicit function definition with missing expression' => [
            [
                '_functions' => [
                    ['compare' => 'exact'],
                ],
            ],
            'The child node "expr" at path "_functions.0" must be configured.',
        ];

        yield 'invalid function compare mode' => [
            [
                '_functions' => [
                    ['expr' => 'A = 1', 'compare' => 'invalid_mode'],
                ],
            ],
            'The value "invalid_mode" is not allowed for path "_functions.0.compare". Permissible values: "exact", "round"',
        ];

        yield 'invalid function precision' => [
            [
                '_functions' => [
                    ['expr' => 'A = 1', 'precision' => 'not_a_number'],
                ],
            ],
            'Invalid type for path "_functions.0.precision". Expected "int", but got "string".',
        ];

        yield 'function precision out of range (too big)' => [
            [
                '_functions' => [
                    ['expr' => 'A = 1', 'precision' => 20],
                ],
            ],
            'The value 20 is too big for path "_functions.0.precision". Should be less than or equal to 10',
        ];

        yield 'function precision out of range (too small)' => [
            [
                '_functions' => [
                    ['expr' => 'A = 1', 'precision' => -1],
                ],
            ],
            'The value -1 is too small for path "_functions.0.precision". Should be greater than or equal to 0',
        ];

        yield 'bad function expression' => [
            [
                '_functions' => [
                    'A = = 1',
                ],
            ],
            "Function definition 'A = = 1' must contain exactly one '=' sign.",
        ];

        yield 'malformed function definition (invalid option)' => [
            [
                '_functions' => [
                    ['expr' => 'A = 1', 'foo' => 'bar'],
                ],
            ],
            'Unrecognized option "foo" under "_functions.0". Available options are "compare", "expr", "precision".',
        ];

        yield 'malformed function definition (invalid nesting)' => [
            [
                '_functions' => [
                    ['expr' => ['Foo']],
                ],
            ],
            'Invalid type for path "_functions.0.expr". Expected "scalar", but got "array".',
        ];

        yield 'malformed static definition (associative list)' => [
            [
                'Foo' => ['A' => 'B'],
            ],
            'Invalid configuration for path "_static.Foo.value": Value \'{"A":"B"}\' should not be associative.',
        ];

        yield 'malformed static definition (nested list)' => [
            [
                'Foo' => ['A', 'B' => ['C']],
            ],
            'Invalid type for path "_static.Foo.value.B". Expected "scalar", but got "array".',
        ];

        yield 'empty static definition' => [
            [
                'Foo' => '',
            ],
            'The path "_static.Foo.value.0" cannot contain an empty value, but got "".',
        ];

        yield 'non-associative static definition' => [
            [
                'Foo',
            ],
            'Static value \'["Foo"]\' must contain a string key.',
        ];

        yield 'static definition with duplicates' => [
            [
                'Foo' => ['A', 'A'],
            ],
            "Static definition 'Foo' contains duplicate values 'A'.",
        ];

        yield 'duplicate names' => [
            [
                '_functions' => [
                    'Foo = 1',
                ],
                'Foo' => 'Bar',
            ],
            "Name 'Foo' cannot appear more than once.",
        ];

        yield 'unresolvable dependencies' => [
            [
                '_functions' => [
                    'A = B',
                    'B = C',
                    'C = A + D',
                ],
                'D' => 1,
            ],
            "There are unresolvable dependencies in function expression(s) 'A', 'B', 'C'.",
        ];
    }

    public function testParseYaml(): void
    {
        $parameters = $this->getParser()->parseYaml("_functions:\n  - A = 1\n  - B = 2\n\nFoo: Bar");

        self::assertCount(3, $parameters);

        $parameterA = $parameters['A'];
        $parameterB = $parameters['B'];
        $parameterFoo = $parameters['Foo'];

        self::assertInstanceOf(FunctionExpressionType::class, $parameterA);
        self::assertEquals('A', $parameterA->getName());
        self::assertEquals('1', $parameterA->getExpression());
        self::assertEquals('exact', $parameterA->getCompareMode());
        self::assertEquals(0, $parameterA->getPrecision());

        self::assertInstanceOf(FunctionExpressionType::class, $parameterB);
        self::assertEquals('B', $parameterB->getName());
        self::assertEquals('2', $parameterB->getExpression());
        self::assertEquals('exact', $parameterB->getCompareMode());
        self::assertEquals(0, $parameterB->getPrecision());

        self::assertInstanceOf(StaticType::class, $parameterFoo);
        self::assertEquals('Foo', $parameterFoo->getName());
        self::assertEquals(['Bar'], $parameterFoo->getOptions());
    }

    private function getParser(): ParameterParser
    {
        return new ParameterParser();
    }
}
